<?

$test_nonadmin_uid1 = 263; /* srivatsan_91 */
$test_nonadmin_uid2 = 402; /* anish shankar */
$test_admin_uid = 262; /* arnstein */

/* uid2 should not be able to access this submissions, but uid1 and admin_uid should be able to */
$submission_owned_by_uid1 = 6290;
$submission_owned_by_uid2 = 6269; 

/* a non-general contest */

$test_non_general_contest = "zco1";
$test_non_general_contest_problem = "zco-2008-1b"; 

/* known submissions with their known scores */
$test_submissions = array (array (6601, 100), 
	array(6696, 64), /* WA, with ICO style grading tested */
	array(6697, 100)); /* TODO: I need a case where OPC style submission would've given a different score */
	


