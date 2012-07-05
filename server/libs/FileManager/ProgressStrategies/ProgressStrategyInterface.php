<?php

interface ProgressStrategyInterface
{
    /**
     * Set Progress Interface
     *
     * @param string $id    Alphanumeric ID
     * @param int $total    Total size
     * @param int $current  Current progress (of total)
     * @return boolean
     */
    public function setProgress($id, $total, $current = 0);

    /**
     * Get the progress
     *
     * @param  string $id     Alphanumeric ID
     * @return object|boolean Properties: (string) id, (float) total, 
     *                        (float) current, (float) percent, (int) updated
     */
    public function getProgress($id);

    /**
     * Clean out old progress items
     *
     * @return int  Number of progress items cleaned
     */
    public function clean();
}

/* ProgressStrategyInterface.php */