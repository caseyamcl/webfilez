<?php

class FileProgressStrategy implements ProgressStrategyInterface
{

    private $path;

    public function __construct()
    {
        if ( ! is_writable(sys_get_temp_dir())) {
            throw new RuntimeException("Cannot use File Progress Strategy if system temp directory not writable");
        }
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
    }

    public function setProgress($id, $total, $current = 0)
    {
        $data = json_encode(array(
            'id'      => $id,
            'total'   => (float) $total,
            'current' => (float) $current,
            'percent' => $current / $total,
            'updated' => time()
        ));

        $fname = $this->path . 'webfilez_upload_progress_' . $id;
        file_put_contents($fname, $data);
    }

    public function getProgress($id)
    {
        $fname = $this->path . 'webfilez_upload_progress_' . $id;

        if (is_readable($fname)) {
            return json_decode(file_get_contents($fname));
        }
        else {
            return FALSE;
        }
    }

    public function clean()
    {
        $filelist = glob($this->path . 'webfilez_upload_progress_*');

        foreach ($filelist as $file) {
            list($null, $id) = explode('_upload_progress_', $file, 2);

            $info = $this->getProgress($id);
            if ((time() - $info->updated) > 60 && $info->percent >= 1) {
                unlink($file);
            }
        }
    }

}

/* FileProgressStrategy.php */