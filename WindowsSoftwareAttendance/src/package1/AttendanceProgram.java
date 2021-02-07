package package1;

import java.awt.EventQueue;

import javax.swing.JFrame;
import javax.swing.JOptionPane;
import javax.swing.JButton;

import java.awt.event.KeyAdapter;
import java.awt.event.KeyEvent;
import java.awt.event.MouseAdapter;
import java.awt.event.MouseEvent;
import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.MalformedURLException;
import java.net.URL;
import javax.swing.JTextField;
import javax.swing.JLabel;
import javax.swing.JCheckBox;
import javax.swing.JPasswordField;

public class AttendanceProgram {

	private JFrame frame;
	private JTextField textField;
	private JPasswordField passwordField;

	/**
	 * Launch the application.
	 */
	public static void main(String[] args) {
		EventQueue.invokeLater(new Runnable() {
			public void run() {
				try {
					AttendanceProgram window = new AttendanceProgram();
					window.frame.setVisible(true);
				} catch (Exception e) {
					e.printStackTrace();
				}
			}
		});
	}

	/**
	 * Create the application.
	 */
	public AttendanceProgram() {
		initialize();
	}

	/**
	 * Initialize the contents of the frame.
	 */
	private void initialize() {
		frame = new JFrame();
		frame.setBounds(100, 100, 450, 300);
		frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
		
		
		

		JButton btnStart = new JButton("Start");
		btnStart.setBounds(59, 170, 117, 25);
		btnStart.addMouseListener(new MouseAdapter() {
			@Override
			public void mouseClicked(MouseEvent arg0) {
				URL starter = null;
				String cid = textField.getText();
				String pid = passwordField.getText();
				
				
				try {
					
					if(cid.equals("100")&&pid.equals("password1"))
					{
					
					// JOptionPane.showMessageDialog(null, textField.getText());
					starter = new URL("http://localhost/akriti/Initiate.php?cid=" + textField.getText().toUpperCase());
					
					}
					
					else
						
					{
						JOptionPane.showMessageDialog(frame, "Sorry, wrong password");
					}
					
				} catch (MalformedURLException e1) {
					// TODO Auto-generated catch block
					e1.printStackTrace();
				}
				BufferedReader in = null;
				String st = "";
				try {
					in = new BufferedReader(new InputStreamReader(starter.openStream()));
					String inputLine;

					while ((inputLine = in.readLine()) != null) {
						// System.out.println(inputLine);
						st += inputLine;
					}
					in.close();
				} catch (IOException e1) {
					// TODO Auto-generated catch block
					e1.printStackTrace();
				}
				JOptionPane.showMessageDialog(null, st);
			}
		});
		frame.getContentPane().setLayout(null);
		frame.getContentPane().add(btnStart);

		JButton btnStop = new JButton("Stop");
		btnStop.setBounds(248, 170, 117, 25);
		btnStop.addMouseListener(new MouseAdapter() {
			@Override
			public void mouseClicked(MouseEvent e) {
				URL verifier = null;
				try {

					// JOptionPane.showMessageDialog(null, textField.getText());
					verifier = new URL("http://localhost/akriti/Verification.php?cid=" + textField.getText().toUpperCase());
				} catch (MalformedURLException e1) {
					// TODO Auto-generated catch block
					e1.printStackTrace();
				}
				BufferedReader in = null;
				String outputStr = "";
				try {
					in = new BufferedReader(new InputStreamReader(verifier.openStream()));
					String inputLine;
					while ((inputLine = in.readLine()) != null) {
						outputStr +=inputLine;
					}
					in.close();
				} catch (IOException e1) {
					// TODO Auto-generated catch block
					e1.printStackTrace();
				}
				JOptionPane.showMessageDialog(null, outputStr);
			}
		});
		frame.getContentPane().add(btnStop);

		JButton btnShowfailedattempts = new JButton("ShowFailedAttempts");
		btnShowfailedattempts.setBounds(59, 232, 306, 25);
		btnShowfailedattempts.addMouseListener(new MouseAdapter() {
			@Override
			public void mouseClicked(MouseEvent e) {
				URL result = null;
				try {
					String w = "http://localhost/akriti/ShowResult.php?cid=" + textField.getText().toUpperCase();
					result = new URL(w);
				} catch (MalformedURLException e1) {
					// TODO Auto-generated catch block
					e1.printStackTrace();
				}
				try {
					BufferedReader in = new BufferedReader(new InputStreamReader(result.openStream()));
					String inputLine;
					String str = "";
					int i = 0;
					while ((inputLine = in.readLine()) != null) {
						if (i == 0) {
							str += inputLine + "\n";
							i = 1;
						} else {
							str += inputLine + "              ";
							i = 0;
						}
					}
					JOptionPane.showMessageDialog(null, str);
					in.close();
				} catch (IOException e1) {
					// TODO Auto-generated catch block
					e1.printStackTrace();
				}

			}
		});
		frame.getContentPane().add(btnShowfailedattempts);

		textField = new JTextField();
		textField.setBounds(59, 115, 117, 19);
		frame.getContentPane().add(textField);
		textField.setColumns(10);
		textField.addKeyListener(new KeyAdapter() {
			@Override
			public void keyTyped(KeyEvent e) {
				if (textField.getText().length() >= 5) // limit to 3 characters
					e.consume();
			}
		});

		JLabel lblCourseid = new JLabel("CourseID");
		lblCourseid.setBounds(59, 92, 70, 15);
		frame.getContentPane().add(lblCourseid);

		JLabel lblWelcome = new JLabel("WELCOME TO ATTENDANCE SYSTEM");
		lblWelcome.setBounds(59, 30, 290, 15);
		frame.getContentPane().add(lblWelcome);
		
		JLabel lblNewLabel = new JLabel("Password");
		lblNewLabel.setBounds(252, 82, 97, 34);
		frame.getContentPane().add(lblNewLabel);
		
		passwordField = new JPasswordField();
		passwordField.setBounds(254, 115, 111, 19);
		frame.getContentPane().add(passwordField);
	}
}
