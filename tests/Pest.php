<?php

use Step2dev\LazySeoTools\Tests\TestCase;

class_alias(TestCase::class, 'Tests\TestCase');

uses(TestCase::class)->in('Feature', 'Unit');
