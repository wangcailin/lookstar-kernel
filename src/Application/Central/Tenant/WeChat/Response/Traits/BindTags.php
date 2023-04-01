<?php

namespace LookstarKernel\Application\Central\Tenant\WeChat\Response\Traits;

trait BindTags
{
    public function bindTags($tag)
    {
        if (is_array($tag)) {
            $this->tag = implode(',', $tag);
        }
    }
}
