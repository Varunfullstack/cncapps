<?php


namespace CNCLTD;


use SplEnum;

class SignableAction extends SplEnum
{
    const __default = 'processing';

    const Signed = "signed";
    const Bounced = "bounced-envelope";

}