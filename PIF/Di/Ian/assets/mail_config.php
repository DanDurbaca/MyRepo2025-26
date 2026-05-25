<?php
// Central mail configuration
// Set the password later (or via env MAIL_PASSWORD) without committing secrets.
return [
    'host' => 'mail.littlespy.org',
    'username' => 'ianstation@littlespy.org',
    // Leave blank; can be overridden at runtime via env MAIL_PASSWORD
    'password' => '-,68,mMokErBa',
    // Optional friendly From name and address (defaults provided if left empty)
    'from' => 'ianstation@littlespy.org',
    'from_name' => 'Portable Indoor Feedback',
];
