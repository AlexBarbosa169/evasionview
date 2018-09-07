<?php
   $capabilities = array(
       'coursereport/evasionview:view' => array(
           'riskbitmask' => RISK_PERSONAL,
           'captype' => 'read',
           'contextlevel' => CONTEXT_COURSE,
           'legacy' => array(               
               'teacher' => CAP_ALLOW,
               'editingteacher' => CAP_ALLOW,
               'admin' => CAP_ALLOW
           )
       ),
       'clonepermissionsfrom' => 'moodle/site:viewreports',
   );
   ?>