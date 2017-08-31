<?php
require_once CLASSES_DIR.'baseenum.php';

abstract class RelationshipTypes extends BasicEnum
{
    const Mother = 1;
    const Father = 2;
    const Grandmother = 3;
    const Grandfather = 4;
    const LegalGuardian = 5;
    const Child = 6;
}