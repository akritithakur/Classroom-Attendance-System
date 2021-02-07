#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
This module begins verification process for Attendance.

It accesses the Pending verification list and populates
list of folders to be verified.

After populating list of folders, it will schedule jobs to verify
and update database accordingly
"""

import face_recognition.api as face_recognition
from multiprocessing import Pool
from multiprocessing import cpu_count
import scipy.misc
import warnings
import re
import click
import MySQLdb
import os
import datetime
import logging
import sys
import subprocess

global curr_date
global course_id

def set_date():

    global curr_date

#    curr_hour = int(datetime.datetime.now().hour)
    curr_date = datetime.date(2018,05,03)
#    curr_date = datetime.date.today().isoformat()
#    print(curr_date)
    return
    
def image_files_in_folder_ref(folder):
    return [os.path.join(folder, f) for f in os.listdir(folder) if re.match(r'.*\.(jpg|jpeg|png)', f, flags=re.I)]

def image_files_in_folder_new(folder):
    return [os.path.join(folder, f) for f in os.listdir(folder) if re.match(r'.*\.(png)', f, flags=re.I)]

def scan_known_people(known_people_folder):
    known_names = []
    known_face_encodings = []

    for file in image_files_in_folder_ref(known_people_folder):
        basename = os.path.splitext(os.path.basename(file))[0]
        img = face_recognition.load_image_file(file)
        encodings = face_recognition.face_encodings(img)

        if len(encodings) > 1:
            click.echo("WARNING: More than one face found in {}. Only considering the first face.".format(file))

        if len(encodings) == 0:
            click.echo("WARNING: No faces found in {}. Ignoring file.".format(file))

        else:
            known_names.append(basename)
            known_face_encodings.append(encodings[0])

    return known_names, known_face_encodings


def test_image(image_to_check, known_names, known_face_encodings, tolerance=0.54):

	unknown_image = face_recognition.load_image_file(image_to_check)

    # Scale down image if it's giant so things run a little faster
	if unknown_image.shape[1] > 1600:
		scale_factor = 1600.0 / unknown_image.shape[1]
		with warnings.catch_warnings():
			warnings.simplefilter("ignore")
			unknown_image = scipy.misc.imresize(unknown_image, scale_factor)

	unknown_encodings = face_recognition.face_encodings(unknown_image)

	match_score = 0
	for unknown_encoding in unknown_encodings:

		distances = face_recognition.face_distance(known_face_encodings, unknown_encoding)
		for distance in distances:
			print(distance)
			if distance <= tolerance:
				match_score+=1

	return match_score

# this function is intended to run in parallel
def verify_for_id(data):
   

	base_path = "/var/www/html/student_images/" + data[0] + "/" # this will be mostly student id
	known_folder_path = base_path + "reference/"
	unknown_folder_path = base_path + data[1].isoformat() + "_" + data[2] + "/"
#	os.system('java -jar transform.jar')
	subprocess.call(["java", "-jar", "tranform.jar",unknown_folder_path])
#	print (subprocess.check_output(["echo", "hello", "world"]))

#	print(unknown_folder_path)
	unknown_image_set = image_files_in_folder_new(unknown_folder_path)

	#print(unknown_image_set)

	known_names, known_face_encodings = scan_known_people(known_folder_path)

	match_score = 0

	for image in unknown_image_set:
		match_score += test_image(image,known_names,known_face_encodings)
		print(match_score)
	print(match_score)
	db = MySQLdb.connect(host="localhost",    # your host, usually localhost
                     user="root",         # your username
                     passwd="12344321",  # your password
                     db="staticdb")        # name of the data base
        
	cur = db.cursor()
	
        if match_score >= 2:
                try:
                    affected_count = cur.execute("""insert into attendance (StudentId,TheDate,CourseId) values (%s,%s,%s)""", (data) )
                    db.commit()
                    affected_count = cur.execute("""delete from nomatch where (StudentId = %s and TheDate = %s and CourseId = %s)""", (data) )
		    db.commit()
		    cur.execute("""delete from pendingattendance where StudentId=%s and TheDate = %s and CourseId = %s""", (data) )
		    db.commit()
#                    logging.warn("%d", affected_count)
#                    logging.info("inserted values %d, %s", id, filename)
                except MySQLdb.IntegrityError:
#                    logging.warn("failed to insert values %d, %s", id, filename)
                     something = "wrong"
                finally:
                   db.close() 
                          	
        else:
                try:
                    affected_count = cur.execute("""insert into nomatch (StudentId,TheDate,CourseId) values (%s,%s,%s)""", (data) )
                    db.commit()
		    cur.execute("""delete from pendingattendance where StudentId=%s and TheDate = %s and CourseId = %s""", (data) )
                    db.commit()
#                    logging.warn("%d", affected_count)
#                    logging.info("inserted values %d, %s", id, filename)
                except MySQLdb.IntegrityError:
#                    logging.warn("failed to insert values %d, %s", id, filename)
                     something = "wrong"
                finally:
                   db.close() 
        
        
	return match_score


def get_data_from_pendingAttendance():

	db = MySQLdb.connect(host="localhost",    # your host, usually localhost
                     user="root",         # your username
                     passwd="12344321",  # your password
                     db="staticdb")        # name of the data base

        global curr_hour # time for the course to be queried
        global curr_date
	cur = db.cursor()
	cur.execute(""" UPDATE startsaver SET Stop= '1' WHERE CourseId = %s and TheDate = %s""", (course_id,curr_date,))
	cur.execute("""select StudentId,TheDate,CourseId from pendingattendance where CourseId = %s and TheDate = %s""", (course_id,curr_date,))
        query_out = cur.fetchall()

        db.commit()
#	query_out = []

#	for row in cur.fetchall():
#    	        print row[0] 
#    	        query_out.append(row)


	db.close()

	return query_out

if __name__ == '__main__':

#	subprocess.check_output(['ls','-l']) #all that is technically needed...

	set_date()
	course_id = (sys.argv[1:]);
#take course_id from professor desktop software input
	pool = Pool(processes = cpu_count())
#	print(cpu_count())
	test = ["a","a","a"]

	query_out = get_data_from_pendingAttendance() 
    

#	query_out = [test for i in range(100)]
#	print(query_out)
	print(datetime.datetime.now().time())
	pool.map(verify_for_id, query_out)
#	print(datetime.datetime.now().time())



