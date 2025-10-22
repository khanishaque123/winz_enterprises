<?php
echo "OpenSSL enabled: " . (extension_loaded('openssl') ? 'YES' : 'NO') . "<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Test basic email function
if (function_exists('mail')) {
    echo "mail() function exists: YES<br>";
} else {
    echo "mail() function exists: NO<br>";
}
?>