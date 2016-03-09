<?php 

defined('MOODLE_INTERNAL') || die();

    $roles = array('zilink_guardians'=>(object) array(
                           'name'=>'ZiLink - Guardians',
                           'description'=>'This ZiLink administered role contains all the capabilities that all guardian users will have',
                           'context' => array(CONTEXT_SYSTEM,CONTEXT_USER),
                           'allowed_capabilities' => array('block/zilink:onlinereporting_viewinformation',
                                                        'block/zilink:onlinereporting_viewoverview',
                                                        'block/zilink:onlinereporting_viewrecent',
                                                        'block/zilink:onlinereporting_viewsubjects',
                                                        'block/zilink:onlinereporting_viewtimetable',
                                                        ),
                        'disallowed_capabilities' => array()
                       ),
                       'editingteacher'=>(object) array(
                        'name'=>'Editing Teacher',
                        'description'=>'',
                        'context' => array(CONTEXT_SYSTEM,CONTEXT_COURSECAT,CONTEXT_COURSE),
                        'allowed_capabilities' => array('block/zilink:roombooking_viewown',
                                                        'block/zilink:tutorview_view',
                                                        'block/zilink:timetable_viewown'),
                                                        
                        'disallowed_capabilities' => array()
                    ),
                );
       
       foreach ($roles as $sname => $role)
       {        
           $mdl_role = $DB->get_record('role',array('shortname' => $sname));
        if (!is_object($mdl_role))                
        {
               $id = create_role($role->name, $sname, $role->description);
               $mdl_role = $DB->get_record('role',array('id'=> $id, 'shortname' => $sname));
        }

        if (is_object($mdl_role))  
        {
            foreach($role->context as $context)
            {
                $mdl_context = $DB->get_record('role_context_levels',array('roleid' => $mdl_role->id, 'contextlevel' => $context));
                if(!is_object($mdl_context))
                {
                    $new_conext                    = new stdClass();
                    $new_conext->roleid            = $mdl_role->id;
                    $new_conext->contextlevel     = $context;
                    $DB->insert_record('role_context_levels', $new_conext);
                }
            }
            
               foreach ($role->allowed_capabilities as $capability)
               {
                   $cap = $DB->get_record('capabilities', array('name' => $capability));
                   if (is_object($cap))
                   {
                       $role_cap = $DB->get_record('role_capabilities', array('contextid' => 1, 'roleid' => $mdl_role->id, 'capability' => $capability));
                       if (!is_object($role_cap))
                       {
                           $new_cap                 = new stdClass();
                           $new_cap->contextid     = 1;
                           $new_cap->roleid         = $mdl_role->id;
                        $new_cap->capability     = $capability;
                        $new_cap->permission     = 1;
                        $new_cap->timemodified    = time();
                        $new_cap->modifierid    = 2;
    
                          $DB->insert_record('role_capabilities', $new_cap);
                      }
                   }
            }
            
            foreach ($role->disallowed_capabilities as $capability)
               {
                   $cap = $DB->get_record('capabilities', array('name' => $capability));
                   if (is_object($cap))
                   {
                       $role_cap = $DB->get_record('role_capabilities', array('contextid' => 1, 'roleid' => $role->id, 'capability' => $capability));
                       if (is_object($role_cap))
                       {
                          $DB->delete_record('role_capabilities', array('id' => $role_cap->id));
                      }
                   }
            }
        }
       }

    $student = $user = $DB->get_record('user', array('username' =>'teststudent'));
    $parent = $user = $DB->get_record('user', array('username' =>'testparent'));
    $role = $DB->get_record('role',array('shortname' => 'zilink_guardians'));
    $context = context_user::instance($student->id);
    role_assign($role->id, $parent->id , $context->id, 'auth_zilink_guardian');
       
       
       
       