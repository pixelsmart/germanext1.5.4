<?php
class IdentityController extends IdentityControllerCore
{
    public function postProcess() {
        if (Tools::isSubmit('submitIdentity')) {
            $this->customer->statistic = (int)Tools::getValue('statistic', 0);
        }
        
        parent::postProcess();
    }
}
