(SELECT 
cn.year as 'Year',
cn.number as 'UniqueNum', 
	(case c.idnumber when '' then '' else CONCAT(c.idnumber,'A', FROM_UNIXTIME(p.timecompleted, '%Y'), 
	FROM_UNIXTIME(p.timecompleted, '%m'), '01',
 	FROM_UNIXTIME(p.timecompleted, '%Y'), 
 	FROM_UNIXTIME(p.timecompleted, '%m'), '01I') end) as 'ID', 
	
	d.data as EmployeeNumber,
	(case p.timecompleted when '' then '' else 'ACCEPTCOMPT' end) as Status,
	CONCAT('Course Completed on ',DATE_FORMAT(FROM_UNIXTIME(p.timecompleted),'%Y-%m-%d'))AS completed
FROM prefix_course_completions AS p
JOIN prefix_course AS c ON p.course = c.id
JOIN prefix_user AS u ON p.userid = u.id
JOIN prefix_user_info_data AS d ON u.id = d.userid,(SELECT @rownum := 5000) as r, prefix_course_numbers as cn 
WHERE c.enablecompletion = 1 AND d.fieldid = 4 AND d.data <> '' AND c.id <> '' AND p.timecompleted <> '' AND c.id=cn.courseid
AND c.coursetype = 'online'
AND FROM_UNIXTIME(p.timecompleted, '%m') = cn.month 
AND FROM_UNIXTIME(p.timecompleted, '%Y') = cn.year
)
UNION ALL
(SELECT
from_unixtime(sd.timestart, '%Y') as 'year',
ss.id + 10000 as 'uniquenum',
CONCAT( CASE c.idnumber WHEN '' THEN c.id ELSE c.idnumber END, 
	'A',
	from_unixtime(sd.timestart,'%Y%m%d'),
	from_unixtime(sd.timefinish,'%Y%m%d'),
	'I') as 'ID',
d.data as 'employeenumber',
CONCAT(
CASE st.statuscode
	WHEN 80 THEN 'NOTCMP'
	WHEN 90 THEN 'NOTCMP'
	WHEN 100 THEN 'COMPT' 
	ELSE 'DATA ERROR'
END,  
CASE st.statuscode
	WHEN 80 THEN 'NOSHOW'
	WHEN 90 THEN 'ACCEPT'
	WHEN 100 THEN 'ACCEPT'
	ELSE 'DATA ERROR'
END) as 'status', 
CONCAT(strain.data, ', ', sloc.data) as 'session loc'
FROM prefix_facetoface_sessions ss
LEFT JOIN prefix_facetoface ff on ss.facetoface = ff.id
LEFT JOIN prefix_facetoface_sessions_dates sd ON ss.id = sd.sessionid
LEFT JOIN prefix_course c on ff.course = c.id
LEFT JOIN prefix_facetoface_signups sup ON ss.id = sup.sessionid
LEFT JOIN prefix_facetoface_signups_status st ON sup.id = st.signupid
LEFT JOIN prefix_facetoface_session_data strain on ss.id = strain.sessionid and strain.fieldid = 2
LEFT JOIN prefix_facetoface_session_data sloc on ss.id = sloc.sessionid and sloc.fieldid = 1
JOIN prefix_user_info_data d ON sup.userid = d.userid and d.fieldid = 4
WHERE st.superceded = 0 AND st.statuscode >= 80
AND c.idnumber <> ''
AND d.data <> '')
ORDER BY uniquenum

