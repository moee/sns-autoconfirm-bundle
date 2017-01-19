<?php

namespace Moee\SnsAutoconfirmBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Moee\SnsAutoconfirmBundle\DependencyInjection\MoeeSnsAutoconfirmExtension;

class MoeeSnsAutoconfirmBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new MoeeSnsAutoconfirmExtension();
        }

        return $this->extension;
    }
}
