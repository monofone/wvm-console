<?php

namespace Blage;

/**
 * Description of EventChecker
 *
 * @author srohweder
 */
class EventChecker
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     *
     * @var Twitter
     */
    protected $twitter;

    /**
     * table containing the calendar events
     * @var type
     */
    protected $eventTable = 'tl_calendar_events';

    /**
     *
     * @var array
     */
    protected $configuration = array(
        'url' => 'http://www.wvm-online.de/terminansicht/events/{ALIAS}.html',
        'message' => 'Morgen am {DATE} ist {TITLE} um {TIME} {URL}'
    );

    /**
     *
     * @param type $db
     * @param Twitter $twitter
     */
    function __construct(\Doctrine\DBAL\Connection $db, \Twitter $twitter, $configuration = array()) {
        $this->db = $db;
        $this->twitter = $twitter;
        $this->configuration = array_merge($this->configuration, $configuration);
    }


    /**
     * fetch events for next day
     */
    protected function fetchUpcomingEvents() {
        $startDateMin = strtotime('tomorrow 0:00:00');
        $startdateMax = strtotime('tomorrow 23:59:59');
        $result = $this->db->fetchAll('SELECT * FROM ' . $this->eventTable . ' WHERE publish_social = 1 AND startDate > ? AND startDate <= ?', array($startDateMin, $startdateMax));

        return $result;
    }

    /**
     * fetch and send the tweeits
     */
    public function sendTweets() {
        $events = $this->fetchUpcomingEvents();
        foreach($events as $event) {
            $this->publishTweetForEvent($event);
        }
    }

    /**
     * send the tweet
     *
     * @param type $event
     */
    protected function publishTweetForEvent($event) {
        $message = $this->buildMessage($event);
        if($this->configuration['debug']){
            echo "sending tweet: ". $message . "\n";
        }else{
            $this->twitter->send($message);
        }
    }

    /**
     * build the message
     *
     * @param type $event
     * @return string
     */
    protected function buildMessage($event) {
        $replacements = array(
            '{DATE}' => date('d.m.Y', $event['startTime']),
            '{TIME}' => date('H:i', $event['startTime']),
            '{TITLE}' => $event['title'],
            '{URL}' => $this->buildUrl($event['alias'])

        );

        return str_replace(array_keys($replacements), array_values($replacements), $this->configuration['message']);
    }

    /**
     *
     * @param type $alias
     * @return type
     */
    protected function buildUrl($alias) {
        $urlLong = urlencode(str_replace('{ALIAS}', $alias, $this->configuration['url']));
        $url = sprintf('https://api-ssl.bitly.com/v3/shorten?login=%s&apiKey=%s&longUrl=%s',
                $this->configuration['bitly_login'],
                $this->configuration['bitly_api_key'],
                $urlLong);
        $data = json_decode(file_get_contents($url));

        return $data->data->url;
    }

}
