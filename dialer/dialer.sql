mysql> describe callouts ;
+----------------+------------------+------+-----+---------------------+----------------+
| Field          | Type             | Null | Key | Default             | Extra          |
+----------------+------------------+------+-----+---------------------+----------------+
| id             | int(10) unsigned | NO   | PRI | NULL                | auto_increment |
| number_to_dial | varchar(256)     | YES  |     | NULL                |                |
| identity       | varchar(256)     | YES  |     | NULL                |                |
| card_no        | varchar(256)     | YES  |     | NULL                |                |
| scheduled_on   | datetime         | NO   |     | 0000-00-00 00:00:00 |                |
| dialed_on      | datetime         | YES  |     | NULL                |                |
| message        | varchar(256)     | YES  |     | NULL                |                |
| uniqueid       | varchar(256)     | YES  |     | NULL                |                |
| tries          | int(2) unsigned  | NO   |     | 0                   |                |
| status         | char(1)          | YES  |     | Q                   |                |
| answer1        | varchar(256)     | YES  |     | NULL                |                |
+----------------+------------------+------+-----+---------------------+----------------+

