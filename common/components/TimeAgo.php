<?php
namespace common\components;

use DateTime;
use DateTimeZone;
use Yii;
use yii\base\Component;
/**
 * AuthHandler handles successful authentication via Yii auth component
 */
class TimeAgo extends Component
{

    public function timeAgo($datetime, $full = false)
    {
        $timezone = new DateTimeZone('Asia/Jakarta');
        $now = new DateTime;
        $now->setTimeZone($timezone);
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'tahun',
            'm' => 'bulan',
            'w' => 'minggu',
            'd' => 'hari',
            'h' => 'jam',
            'i' => 'menit',
            's' => 'detik',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v;
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' lalu' : 'baru saja';
    }

}