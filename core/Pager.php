<?php

class Pager
{
    private $total_records;
    private $per_page_records;
    private $max_pager_range;
    private $current_page;

    public function __construct($total_records, $max_pager_range, $per_page_records)
    {
        $this->total_records = $total_records;
        $this->max_pager_range = $max_pager_range;
        $this->per_page_records = $per_page_records;
    }

    public function setCurrentPage($page)
    {
        if (!is_numeric($page)) {
            $this->current_page = 1;
        } else if ($page <= 0) {
            $this->current_page = 1;
        } else if ($page > $this->getTotalPages()) {
            $this->current_page = $this->getTotalPages();
        } else {
            $this->current_page = (int)$page;
        }
    }

    public function getTotalRecords()
    {
        return $this->total_records;
    }

    public function getPerPageRecords()
    {
        return $this->per_page_records;
    }

    public function getCurrentPage()
    {
        return $this->current_page;
    }

    public function getPageNumbers()
    {
        $range = $this->getBothRanges();

        if ($this->current_page <= $range['left']) {
            $start = 1;
            $end = min($this->max_pager_range, $this->getTotalPages());
        } else if ($this->current_page < $this->getTotalPages() - $range['right']) {
            $start = $this->current_page - $range['left'];
            $end   = $this->current_page + $range['right'];
        } else {
            $start = max($this->getTotalPages() - $this->max_pager_range + 1, 1);
            $end   = $this->getTotalPages();
        }

        $page_numbers = [];
        for ($i = $start; $i <= $end; $i++) {
            $page_numbers[] = $i;
        }
        return $page_numbers;
    }

    public function getOffset()
    {
        $offset = $this->per_page_records * ($this->current_page - 1);

        if ($offset < 0) {
            return 0;
        } else {
            return $offset;
        }
    }

    public function hasPreviousPage()
    {
        if ($this->getCurrentPage() > 1) {
            return true;
        } else {
            return false;
        }
    }

    public function hasNextPage()
    {
        if ($this->current_page < $this->getTotalPages()) {
            return true;
        } else {
            return false;
        }
    }

    public function getPreviousPage()
    {
        return $this->current_page - 1;
    }

    public function getNextPage()
    {
        return $this->current_page + 1;
    }

    private function getBothRanges()
    {
        $both_ranges = [];
        if (($this->max_pager_range % 2) === 1) {
            $both_ranges['left'] = ((int)ceil($this->max_pager_range - 1) / 2);
            $both_ranges['right'] = ((int)floor($this->max_pager_range - 1) / 2);
            return $both_ranges;
        } else {
            $both_ranges['left'] = (int)ceil($this->max_pager_range / 2);
            $both_ranges['right'] = ((int)floor($this->max_pager_range - 1) / 2);
            return $both_ranges;
        }
    }

    private function getTotalPages()
    {
        return (int)ceil($this->total_records / $this->per_page_records);
    }
}
