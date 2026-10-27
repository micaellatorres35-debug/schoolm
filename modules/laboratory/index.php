<?php

require_once __DIR__ . '/config/config.php';

//controllers
require_once __DIR__ . '/app/controllers/ApprovalDecisionSupportController.php';

session_start();

$basePath = BASE_URL;
$uri = trim(str_replace($basePath, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), '/');

$segments = explode('/', $uri);

switch ($segments[0] ?? '') {

    case '':
        require_once __DIR__ . '/app/controllers/HomeController.php';
        (new HomeController())->index();
        break;

    case 'borrow':

        require_once __DIR__ . '/app/controllers/PhysBrwController.php';
        $controller = new PhysBrwController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'delete' && isset($segments[2])) {
            $controller->delete($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
        }

        break;

    case 'settings':

        require_once __DIR__ . '/app/controllers/SettingsController.php';
        $controller = new SettingsController();

        if (!isset($segments[1])) {
            // /settings
            $controller->index();
        } elseif ($segments[1] === 'update') {
            // /settings/update
            $controller->update();
        }

        break;


    case 'physics-damage':
        require_once __DIR__ . '/app/controllers/PhyDamageController.php';
        $controller = new PhyDamageController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);

        } elseif ($segments[1] === 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;

    case 'physics-inventory':
        require_once __DIR__ . '/app/controllers/PhysInventoryController.php';
        $controller = new PhysInventoryController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;

    case 'psycho-inventory':
        require_once __DIR__ . '/app/controllers/PsychoInvController.php';
        $controller = new PsychoInvController();

         if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
        
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;

    case 'he-inventory':
        require_once __DIR__ . '/app/controllers/HeInvController.php';
        $controller = new HeInvController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;

    case 'crim-inventory':
        require_once __DIR__ . '/app/controllers/CrimInvController.php';
        $controller = new CrimInvController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'delete' && isset($segments[2])) {
            $controller->destroy($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
        }

        break;

    case 'phys-monitoring':
        require_once __DIR__ . '/app/controllers/PhysMonitoringController.php';
        $controller = new PhysMonitoringController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;

    case 'fingerprint-inventory':
        require_once __DIR__ . '/app/controllers/FingerprintInvController.php';
        $controller = new FingerprintInvController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
        
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'questioned-inventory':
        require_once __DIR__ . '/app/controllers/QuestionedInvController.php';
        $controller = new QuestionedInvController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
        
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'chemestry-inventory':
        require_once __DIR__ . '/app/controllers/ChemestryInvController.php';
        $controller = new ChemestryInvController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
            
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }
        
        break;

    case 'defense-inventory':
        require_once __DIR__ . '/app/controllers/DefenseInvController.php';
        $controller = new DefenseInvController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
            
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;

    case 'crim-borrow':
        require_once __DIR__ . '/app/controllers/CrimBrwController.php';
        (new CrimBrwController())->index();
        break;

    case 'it-inventory':
        require_once __DIR__ . '/app/controllers/ItInventoryController.php';
        (new ItInventoryController())->index();
        break;

    case 'crim-damage':
        require_once __DIR__ . '/app/controllers/CrimDmgController.php';
        (new CrimDmgController())->index();
        break;

    case 'phys_borrow':
        require_once __DIR__ . '/app/controllers/PhysBrwController.php';
        (new PhysBrwController())->index();
        break;


    case 'schedule':

    require_once __DIR__ . '/app/controllers/ScheduleController.php';

    $controller = new ScheduleController();

    // /schedule
    if (!isset($segments[1])) {

        $controller->index();

    }

    // /schedule/create
    elseif ($segments[1] === 'create') {

        $controller->create();

    }

    // /schedule/view/5
    elseif ($segments[1] === 'view' && isset($segments[2])) {

        $controller->view($segments[2]);

    }

    // /schedule/update/5
    elseif ($segments[1] === 'update' && isset($segments[2])) {

        $controller->update($segments[2]);

    }

    // /schedule/delete/5
    elseif ($segments[1] === 'delete' && isset($segments[2])) {

        $controller->delete($segments[2]);

    }

    elseif ($segments[1] === 'getSchedules') {
        $controller->allSchedules();
    }

    else {
        http_response_code(404);
        echo "404 Not Found";
    }

    break;
    

    case 'it_damage':
        require_once __DIR__ . '/app/controllers/ItDmgController.php';
        $controller = new ItDmgController();
        
        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
                   
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;

    // case 'it_borrow':
    //     require_once __DIR__ . '/app/controllers/ItBrwController.php';
    //     $labId = $segments[2] ?? 1;
    //     (new ItBrwController())->index($labId);
    //     break;


    case 'fingerprint-damage':
        require_once __DIR__ . '/app/controllers/FingerprintDmgController.php';
        $controller = new FingerprintDmgController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
            
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'crime-scene-damage':
        require_once __DIR__ . '/app/controllers/CrimeSceneDmgController.php';
        $controller = new CrimeSceneDmgController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
            
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'balistic-damage':
        require_once __DIR__ . '/app/controllers/BalisticDmgController.php';
        $controller = new BalisticDmgController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
            
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'fingerprint-borrow':
        require_once __DIR__ . '/app/controllers/FingerprintBrwController.php';
        $controller = new FingerprintBrwController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
        } elseif ($segments[1] == 'delete' && isset($segments[2])) {
            $controller->delete($segments[2]);
        }

        break;



    case 'crimescene-borrow':
        require_once __DIR__ . '/app/controllers/CrimesceneBrwController.php';
        $controller = new CrimesceneBrwController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
        } elseif ($segments[1] == 'delete' && isset($segments[2])) {
            $controller->delete($segments[2]);
        }

        break;



    case 'balistic-borrow':
        require_once __DIR__ . '/app/controllers/BalisticBrwController.php';
        $controller = new BalisticBrwController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
        } elseif ($segments[1] == 'delete' && isset($segments[2])) {
            $controller->delete($segments[2]);
        }

        break;


    case 'questiondocument-borrow':
        require_once __DIR__ . '/app/controllers/QuestiondocumentBrwController.php';
        $controller = new QuestiondocumentBrwController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
        } elseif ($segments[1] == 'delete' && isset($segments[2])) {
            $controller->delete($segments[2]);
        }

        break;


    case 'chemistry-borrow':
        require_once __DIR__ . '/app/controllers/ChemistryBrwController.php';
        $controller = new ChemistryBrwController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
        } elseif ($segments[1] == 'delete' && isset($segments[2])) {
            $controller->delete($segments[2]);
        }

        break;


    case 'defense-tactics-borrow':
        require_once __DIR__ . '/app/controllers/DefenseTacticsBrwController.php';
        $controller = new DefenseTacticsBrwController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
        } elseif ($segments[1] == 'delete' && isset($segments[2])) {
            $controller->delete($segments[2]);
        }

        break;


    case 'chemistry-damage':
        require_once __DIR__ . '/app/controllers/ChemistryDmgController.php';
        $controller = new ChemistryDmgController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
            
         } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;



    case 'defense-tactics-damage':
        require_once __DIR__ . '/app/controllers/DefenseTacticsDmgController.php';
        $controller = new DefenseTacticsDmgController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
            
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'question-document-damage':
        require_once __DIR__ . '/app/controllers/QuestionDocumentDmgController.php';
        $controller = new QuestionDocumentDmgController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
           
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'lab1-monitoring':
        require_once __DIR__ . '/app/controllers/Lab1MonitoringController.php';
        $controller = new Lab1MonitoringController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;

    case 'lab2-monitoring':
        require_once __DIR__ . '/app/controllers/Lab2MonitoringController.php';
        $controller = new Lab2MonitoringController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;

    case 'lab3-monitoring':
        require_once __DIR__ . '/app/controllers/Lab3MonitoringController.php';
        $controller = new Lab3MonitoringController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'psy_monitoring':
         require_once __DIR__ . '/app/controllers/PsyMonitoringController.php';
        $controller = new PsyMonitoringController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
        
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;    


    case 'psy_borrow':
         require_once __DIR__ . '/app/controllers/PsyBorrowController.php';
        $controller = new PsyBorrowController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
        } elseif ($segments[1] === 'delete' && isset($segments[2])) {
            $controller->delete($segments[2]);
        }

        break;


    case 'he_monitoring':
        require_once __DIR__ . '/app/controllers/HeMonitoringController.php';
        $controller = new HeMonitoringController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;

    case 'he_damage':
        require_once __DIR__ . '/app/controllers/HeDamageController.php';
        $controller = new HeDamageController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
                   
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'he_borrow':
        require_once __DIR__ . '/app/controllers/HeBorrowController.php';
        $controller = new HeBorrowController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;
        

    case 'fingerprint-monitoring':
        require_once __DIR__ . '/app/controllers/FingerprintMonitoringController.php';
        $controller = new FingerprintMonitoringController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'crime-scene-monitoring':
        require_once __DIR__ . '/app/controllers/CrimeSceneMonitoringController.php';
        $controller = new CrimeSceneMonitoringController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();
                   
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'ballistic-monitoring':
        require_once __DIR__ . '/app/controllers/BallisticMonitoringController.php';
        $controller = new BallisticMonitoringController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
            
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }
        break;


    case 'question-document-monitoring':
        require_once __DIR__ . '/app/controllers/QuestionDocumentMonitoringController.php';
        $controller = new QuestionDocumentMonitoringController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] == 'create') {
            $controller->create();
        } elseif ($segments[1] == 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] == 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;



    case 'chemistry-monitoring':
        require_once __DIR__ . '/app/controllers/ChemistryMonitoringController.php';
        $controller = new ChemistryMonitoringController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
        
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;



    case 'defense-tactics-monitoring':
        require_once __DIR__ . '/app/controllers/DefenseTacticsMonitoringController.php';
        $controller = new DefenseTacticsMonitoringController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
        
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }
        break;

   case 'it-lab3-inventory':
        require_once __DIR__ . '/app/controllers/Itlab3InventoryController.php';
        $controller = new Itlab3InventoryController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;

    case 'it-lab2-inventory':
        require_once __DIR__ . '/app/controllers/ItLab2InventoryController.php';
        $controller = new ItLab2InventoryController();
        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;

    case 'it-lab1-inventory':

        require_once __DIR__ . '/app/controllers/Itlab1InventoryController.php';

        $controller = new Itlab1InventoryController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;

        
    case 'crime-scene-inventory':

        require_once __DIR__ . '/app/controllers/CrimeSceneInvController.php';

        $controller = new CrimeSceneInvController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'balistic-inventory':
        require_once __DIR__ . '/app/controllers/BalisticInvController.php';
        $controller = new BalisticInvController();

         if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
            
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }
        
        break;


    case 'lab2-damage':
        require_once __DIR__ . '/app/controllers/Itlab2DamageController.php';
        $controller = new Itlab2DamageController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
                  
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }
        break;


    case 'lab3-damage':
        require_once __DIR__ . '/app/controllers/Itlab3DamageController.php';
        $controller = new Itlab3DamageController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;
        


    case 'lab1-borrow':
        require_once __DIR__ . '/app/controllers/Itlab1BorrowController.php';
        $controller = new Itlab1BorrowController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'lab2-borrow':
        require_once __DIR__ . '/app/controllers/Itlab2BorrowController.php';
        $controller = new Itlab2BorrowController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
        
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'lab3-borrow':
        require_once __DIR__ . '/app/controllers/Itlab3BorrowController.php';
        $controller = new Itlab3BorrowController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();
            
        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }

        break;


    case 'psycho-damage':
        require_once __DIR__ . '/app/controllers/PsychoDmgController.php';
        $controller = new PsychoDmgController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'view' && isset($segments[2])) {
            $controller->view($segments[2]);
        } elseif ($segments[1] === 'update') {
            $controller->update();

        } elseif ($segments[1] == 'inactive') {
            $controller->inactive();

        } elseif ($segments[1] == 'deactivate' && isset($segments[2])) {
            $controller->deactivate($segments[2]);

        } elseif ($segments[1] == 'activate' && isset($segments[2])) {
            $controller->activate($segments[2]);
        }
        break;


    case 'approval-decision-support':
        require_once __DIR__ . '/app/controllers/ApprovalDecisionSupportController.php';
        $controller = new ApprovalDecisionSupportController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'update-decision') {
            $controller->updateDecision();
        } elseif ($segments[1] === 'delete') {
            $controller->delete();
        };
        break;

    case 'concern-issue-tracking':
        require_once __DIR__ . '/app/controllers/ConcernIssueTrackingController.php';
        $controller = new ConcernIssueTrackingController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'resolve') {
            $controller->resolve();
        } elseif ($segments[1] === 'delete') {
            $controller->delete();
        };
        break;

    case 'report-submission-management':
        require_once __DIR__ . '/app/controllers/ReportSubmmisionManagementController.php';
        $controller = new ReportSubmmisionManagementController();

        if (!isset($segments[1])) {
            $controller->index();
        } elseif ($segments[1] === 'create') {
            $controller->create();
        } elseif ($segments[1] === 'delete') {
            $controller->delete();
        };
        break;

    default:
        http_response_code(404);
        require_once __DIR__ . '/app/views/errors/404.php';
}
