<?php
require_once 'lib/Command.php';

$cmd=new Command('Title', '/thumn.png');

echo $cmd->make_command(
    ['input.mkv','mark.png'],
    'output_720',
    'output_480',
    'stats.txt',
    0
);
