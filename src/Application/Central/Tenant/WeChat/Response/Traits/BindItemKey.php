<?php

namespace LookstarKernel\Application\Central\Tenant\WeChat\Response\Traits;

trait BindItemKey
{
    public function BindItemKey($itemKey)
    {
        if ($itemKey) {
            $this->itemKey = $itemKey;
        }
    }
}
