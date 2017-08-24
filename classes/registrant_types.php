<?php
require_once CLASSES_DIR.'baseenum.php';

abstract class RegistrantTypes extends BasicEnum
{
    const Student = 1;
    const Parent = 2;
    const Mentor = 3;
    const Alumni = 4;
}

//$today = DaysOfWeek::Sunday;