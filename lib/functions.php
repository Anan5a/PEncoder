<?php


function add_process($command, $max=1)
{
    $running_list = get_process($command);
    $running_count = count($running_list);

    if ($running_count >= $max) {
        return false;
    }
    //execute command
    shell_exec($command);
    return get_process($command);
}


function get_process($command)
{
    $retval = [];
    $list_data = [];
    $bin = explode(' ', ltrim($command, ' '))[0];

    //get info
    $running = shell_exec('ps axu|grep '.$bin);
    foreach (explode(PHP_EOL, $running) as $running_cur) {
        if (preg_match("/(axu\|grep|grep)/i", $running_cur)) {
            continue;
        }
        $val_to_append = null;
        foreach (explode(' ', $running_cur) as $parts) {
            if (!empty($parts)) {
                $val_to_append[] = $parts;
            } else {
                continue;
            }
        }
        if (!empty($val_to_append)) {
            $list_data[] = $val_to_append;
        }
    }
    foreach ($list_data as $data) {
        $retval[] = build_data($data);
    }
    return $retval;
}

function build_data($array_data)
{
    $cmd = '';
    for ($i=0; $i < count($array_data); $i++) {
        if ($i<10) {
            continue;
        }
        $cmd .= $array_data[$i].' ';
    }
    return [
        'user'=>$array_data[0],
        'pid'=>$array_data[1],
        'cmd'=>$cmd
    ];
}


function read_progress()
{
}
