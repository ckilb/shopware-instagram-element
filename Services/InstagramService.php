<?php
/**
 * Class InstagramService
 *
 * Diese Klasse enthält Logik zum Abfrufen der Instagram-Bilder.
 * Der Code dieser Klasse könnte auch direkt in der Bootstrap.php implementiert werden.
 * Ich empfehle aber, Business-Logik immer auszulagern, statt sie in Bootstrap-Dateien oder Controllern zu verwenden.
 * Das macht den Code übersichtlicher und macht eine Wiederverwendbarkeit möglich.
 * Zudem wäre es möglich, Service-Klassen mit Unit-Tests zu covern.
 *
 * @author Christian Kilb
 */
class InstagramService
{
    /**
     * Unter dieser URL bietet Instagram einen Endpoint an, um im JSON Format Bild-URLs auszulesen.
     * Das %s wird mittels sprintf() mit dem Usernamen ersetzt.
     */
    const INSTAGRAM_MEDIA_URL = 'https://www.instagram.com/%s/media/';

    /**
     * Es wäre sehr inperformant, wenn wir bei jedem Seitenaufruf die Bilder erneut von Instagram abfragen.
     * Shopware bietet ein Cache-System, das wir uns zur Nutze machen. Hierfür brauchen wir einen eindeutigen
     * Cache-Key, der von keinem anderen Plugin oder Shopware selbst bereits verwendet wird.
     */
    const CACHE_KEY = 'instagram_element_images';

    /**
     * @var Zend_Cache_Core
     */
    private $cache;

    /**
     * @param Zend_Cache_Core $cache
     */
    public function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $username
     * @param int $limit
     * @return string[]
     */
    public function getImageUrls($username, $limit)
    {
        $cacheKey = $this->generateCacheKey($username, $limit);
        $cached   = $this->cache->load($cacheKey);
        $limit    = $limit ? $limit : PHP_INT_MAX;

        if ($cached) {
            return $cached;
        }

        $images   = [];
        $maxId    = null;

        do {
            $result = $this->fetch($username, $maxId);
            $maxId  = $this->parseMaxId($result);
            $images = array_merge($images, $this->parseImageUrls($result));
        } while ($result->more_available && $maxId && count($images) < $limit);

        $images = array_reverse($images); // neueste Bilder zuerst
        $images = array_chunk($images, $limit)[0]; // Limit einhalten

        $this->cache->save($images, $cacheKey);

        return $images;
    }


    /**
     * @param string $username
     * @param int|null $maxId
     * @return array JSON result
     */
    private function fetch($username, $maxId = null)
    {
        $url = sprintf(self::INSTAGRAM_MEDIA_URL, $username);

        if ($maxId) {
            $url .= '?max_id=' . $maxId;
        }

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        $json   = json_decode($result);

        curl_close($curl);

        return $json;
    }

    /**
     * @param array $result
     * @return int|null
     */
    private function parseMaxId($result)
    {
        if (!count($result->items)) {
            return null;
        }

        return array_pop($result->items)->id;
    }

    /**
     * @param array $result
     * @return string[]
     */
    private function parseImageUrls($result)
    {
        $urls = [];
        foreach ($result->items as $item) {
            $image = $this->parseLargestImage($item);

            if ($image) {
                $urls[] = $image->url;
            }
        }

        return $urls;
    }

    /**
     * @param array $item
     * @return array|null
     */
    private function parseLargestImage($item)
    {
        $width        = 0;
        $largestImage = null;

        foreach ($item->images as $image) {
            if ($image->width > $width) {
                $largestImage = $image;
            }
        }

        return $largestImage;
    }

    /**
     * @param string $username
     * @param int $limit
     * @return string
     */
    private function generateCacheKey($username, $limit)
    {
        return implode('_', [
            self::CACHE_KEY,
            $username,
            $limit
        ]);
    }

}