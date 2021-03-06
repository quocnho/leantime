<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class simpleCanvas
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $leancanvasRepo = new repositories\leancanvas();

            $allCanvas = $leancanvasRepo->getAllCanvas($_SESSION['currentProject']);

            if(isset($_SESSION['currentLeanCanvas'])) {
                $currentCanvasId = $_SESSION['currentLeanCanvas'];
            }else{
                $currentCanvasId = -1;
                $_SESSION['currentLeanCanvas'] = "";
            }

            if (count($allCanvas) > 0 && $_SESSION['currentLeanCanvas'] == '') {
                $currentCanvasId = $allCanvas[0]['id'];
                $_SESSION['currentLeanCanvas'] = $currentCanvasId;
            }

            if (isset($_GET["id"]) === true) {
                $currentCanvasId = (int)$_GET["id"];
                $_SESSION['currentLeanCanvas'] = $currentCanvasId;
            }

            if (isset($_POST["searchCanvas"]) === true) {
                $currentCanvasId = (int)$_POST["searchCanvas"];
                $_SESSION['currentLeanCanvas'] = $currentCanvasId;
            }

            //Add Canvas
            if (isset($_POST["newCanvas"]) === true) {

                if (isset($_POST['canvastitle']) === true) {

                    $values = array("title" => $_POST['canvastitle'], "author" => $_SESSION['userdata']["id"], "projectId" => $_SESSION["currentProject"]);
                    $currentCanvasId = $leancanvasRepo->addCanvas($values);
                    $allCanvas = $leancanvasRepo->getAllCanvas($_SESSION['currentProject']);

                    $_SESSION["msg"] = "NEW_CANVAS_ADDED";
                    $_SESSION["msgT"] = "success";


                    $mailer = new core\mailer();
                    $projectService = new services\projects();
                    $users = $projectService->getUsersToNotify($_SESSION['currentProject']);

                    $mailer->setSubject("A new research canvas was created");

                    $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                    $mailer->setHtml("A new lean canvas was created by " . $_SESSION["userdata"]["name"] . ": <a href='" . $actual_link . "'>" . $values['title'] . "</a>.<br />");
                    $mailer->sendMail($users, $_SESSION["userdata"]["name"]);

                    $tpl->setNotification('New canvas created successfully. Now who is your customer?', 'success');

                    $tpl->redirect("/leancanvas/simpleCanvas/".$currentCanvasId);

                } else {
                    $tpl->setNotification('ENTER_TITLE', 'error');
                }

            }

            //Add Canvas Item
            if (isset($_POST["addItem"]) === true) {

                if (isset($_POST['description']) === true) {

                    $currentCanvasId = (int)$_SESSION['currentCanvas'];

                    $values = array(
                        "box" => $_POST['box'],
                        "author" => $_SESSION['userdata']["id"],
                        "description" => $_POST['description'],
                        "status" => $_POST['status'],
                        "assumptions" => $_POST['assumptions'],
                        "data" => $_POST['data'],
                        "conclusion" => $_POST['conclusion'],
                        "canvasId" => $currentCanvasId
                    );

                    $leancanvasRepo->addCanvasItem($values);

                    $_SESSION["msg"] = "NEW_CANVAS_ITEM_ADDED";
                    $_SESSION["msgT"] = "success";

                    $tpl->setNotification('New item created successfully.', 'success');

                    $tpl->redirect("/leancanvas/showCanvas/" . $currentCanvasId);

                } else {
                    $tpl->setNotification('ENTER_TITLE', 'error');
                }
            }

            if (isset($_POST["editItem"]) === true) {

                if (isset($_POST['description']) === true) {

                    $currentCanvasId = (int)$_SESSION['currentCanvas'];

                    $values = array(
                        "box" => $_POST['box'],
                        "author" => $_SESSION['userdata']["id"],
                        "description" => $_POST['description'],
                        "status" => $_POST['status'],
                        "assumptions" => $_POST['assumptions'],
                        "data" => $_POST['data'],
                        "conclusion" => $_POST['conclusion'],
                        "itemId" => $_POST['itemId'],
                        "canvasId" => $currentCanvasId
                    );

                    $leancanvasRepo->editCanvasItem($values);

                    $_SESSION["msg"] = "NEW_CANVAS_ITEM_ADDED";
                    $_SESSION["msgT"] = "success";
                    header("Location: /leancanvas/showCanvas/" . $currentCanvasId);

                } else {
                    $tpl->setNotification('ENTER_TITLE', 'error');
                }

            }

            $tpl->assign('currentCanvas', $currentCanvasId);
            $tpl->assign('canvasLabels', $leancanvasRepo->canvasTypes);
            $tpl->assign('allCanvas', $allCanvas);
            $tpl->assign('canvasItems', $leancanvasRepo->getCanvasItemsById($currentCanvasId));


            if (isset($_GET["raw"]) === false) {
                $tpl->display('leancanvas.simpleCanvas');
            }
        }

    }

}


