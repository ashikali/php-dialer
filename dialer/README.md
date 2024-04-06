This application is a PHP-based dialer that retrieves call requests from a database. 
It utilizes the Asterisk Manager Interface (AMI) to dispatch dial requests to the Asterisk server. 
Subsequent to dialing, the associated context will be executed.



GRANT ALL ON dialer.* TO 'asterisk'@'localhost' IDENTIFIED BY 'asterisk';

[cti_service]
secret=cti_service
deny=0.0.0.0/0.0.0.0
permit=127.0.0.1/255.255.255.0
read=agent,call
write=system,call,log,verbose,command,agent,user

