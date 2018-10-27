<?php
class Command
{
    const FFMPEG = 'ffmpeg ';
    const FFMPEG_FILTER_COMPLEX = '-filter_complex';
    const FFMPEG_FILTER_OVERLAY_TOP_RIGHT = 'overlay=W-w-5:5,';
    const FFMPEG_FILTER_SPLIT = 'split=%d%s';
    const FFMPEG_VIDEO_0 = '[v:0]';
    const FFMPEG_MAP_720_X264 = '-map \'%s\' -s 1280:720 %s -c:a copy -c:v libx264 -crf %s %s';
    const FFMPEG_MAP_480_X264 = '-map \'%s\' -s 960:480 %s -c:a copy -c:v libx264 -crf %s %s';
    const FFMPEG_INPUT = '-i %s';
    const FFMPEG_STATS = '-vstats_file %s';

    const LINUX_IO_REDIR = '>/dev/null 2>&1';
    private $title = '';
    private $image='';
    private $encode_length='';

    public function __construct($title, $image)
    {
        $this->title=$title;
        $this->image=$image;
    }

    function setDuration($start,$length){	    $this->encode_length = " -ss $start -t $length ";
    }
    public function make_command($input, $output1, $output2, $stat_file, $crf=30, $io_redir=true)
    {
        if (empty($input)) {
            exit('Specify input');
        }
        if (($output1==null)&&($output2==null)) {
            exit('Specify atleast 1 output');
        }
        $split = 0;

        if ($output1!=null) {
            $split+=1;
        }
        if ($output2!=null) {
            $split+=1;
        }
        $split_map = $this->build_map($split);
	$cmd = self::FFMPEG;
	$cmd .= $this->encode_length;
        if (!is_array($input)) {
            $cmd .= sprintf(self::FFMPEG_INPUT, $input);
        } else {
            $cmd .= sprintf(self::FFMPEG_INPUT, $input[0]);
        }
        $cmd .= $this->build_filter($input[1], $split);
        $map1=$split_map[0];
        $map2=isset($split_map[1])?$split_map[1]:'';

        if ($output1==null) {
            $map2=$split_map[0];
        }
        if ($output2==null) {
            $map1=$split_map[0];
        }
        if ($output1 != null) {
            $cmd .= $this->build_export_cmd(self::FFMPEG_MAP_720_X264, $map1, $crf, $output1);
        }
        if ($output2 != null) {
            $cmd .= $this->build_export_cmd(self::FFMPEG_MAP_480_X264, $map2, $crf, $output2);
        }
        if ($io_redir==true) {
            $cmd .= self::LINUX_IO_REDIR;
        }
        return $cmd;
    }

    private function build_map($split)
    {
        $ret=[];
        for ($i=0; $i < $split; $i++) {
            $ret[]="[out$i]";
        }
        return $ret;
    }
    private function build_filter($image, $split, $type='watermark')
    {
        return sprintf(
            " %s %s '%s%s%s' ",
            sprintf(self::FFMPEG_INPUT, $image),
            self::FFMPEG_FILTER_COMPLEX,
	    self::FFMPEG_VIDEO_0,
	    self::FFMPEG_FILTER_OVERLAY_TOP_RIGHT,
            sprintf(self::FFMPEG_FILTER_SPLIT, $split, str_replace(
                ' ',
                '',
                            join(' ', $this->build_map($split))
            ))
        );
    }
    private function build_stats_file($file)
    {
        return sprintf(self::FFMPEG_STATS, $file);
    }

    private function build_export_cmd($template, $map_name, $crf=30, $output)
    {
        return sprintf(" $template ", $map_name, $this->metadata($this->title, $this->image), $crf, $output);
    }

    private function metadata($title, $image)
    {
        return " -map_metadata -1 -metadata title='$title' -metadata image='$image'";
    }
}
