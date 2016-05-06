<?php


class PublicAction extends Action
{

    public function login()
    {
        $passport = service('Passport');
        if ($passport->isLogged()) {
            if (isset($_GET['refer'])) {
                redirect($_GET['refer']);
            }

            if (strpos($_SERVER['REQUEST_URI'], '/index.php?app=pufinance&mod=Public&act=login') === 0) {
                $this->redirect('pufinance/PuCredit/index');
            }
            $this->redirect($_SESSION['refer_url']);
        }
        $this->display();
    }
}