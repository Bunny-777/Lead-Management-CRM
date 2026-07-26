<?php
// logout.php
session_start();
session_unset();
session_destroy();
header("Location: /CRM%20P/login.php?msg=" . urlencode("Logged out successfully"));
exit();
