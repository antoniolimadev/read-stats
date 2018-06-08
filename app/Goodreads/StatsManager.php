<?php

namespace App\Goodreads;
use \App\Goodreads\Book;

class StatsManager
{
    protected $userId;
    protected $apikey;
    protected $api;

    public function __construct($userId) {
        $this->userId = $userId;
        $this->api = resolve('App\Goodreads\ApiRequest');
    }

     public function getUserId() { return $this->userId; }

    public function XMLtoArray($xml){

        $json = json_encode($xml);
        $array = json_decode($json,TRUE);
        return $array;
    }

    public function saveUserInfoXML($userId){
        $xmlResponse = $this->api->getUserInfo($userId);
        if (!$xmlResponse){ return false; }
        $fileName = config('goodreads.storage.storage_folder') .
                    $userId .
                    config('goodreads.storage.xml_user_info') .
                    config('goodreads.storage.xml_extension');
        $xmlResponse->saveXML(storage_path($fileName));
        return true;
    }

    // read shelves with more than 200 books
    public function saveShelfReadXML($userId, $index = 1){
        $pageIndex = $index;
        // saves current page to file (starts at 1)
        $xmlResponse = $this->api->getShelfRead($userId, $pageIndex);
        if (!$xmlResponse){ return false; }
        $fileName = config('goodreads.storage.storage_folder') .
                    $userId .
                    config('goodreads.storage.xml_shelf_read') .
                    $pageIndex .
                    config('goodreads.storage.xml_extension');
        $xmlResponse->saveXML(storage_path($fileName));

        // checks if there are more books
        // <reviews start="1" end="200" total="365">
        $xmlReviews = $xmlResponse->reviews;
        $endNumberOfReviews = (int) $xmlReviews['end'];
        $totalNumberOfReviews = (int) $xmlReviews['total'];

        //echo $endNumberOfReviews . " < " . $totalNumberOfReviews;

        // if 'end' is smaller than 'total' then it must fetch the rest of the books
        if ($endNumberOfReviews < $totalNumberOfReviews) {
            $pageIndex++;
            $this->saveShelfReadXML($userId, $pageIndex);
        }
        return true;
    }

    public function readUserInfoXML($userId){
        $fileName = config('goodreads.storage.storage_folder') .
                    $userId .
                    config('goodreads.storage.xml_user_info') .
                    config('goodreads.storage.xml_extension');
        $userDataXML = simplexml_load_file(storage_path($fileName)) or die("Error: Cannot read userInfo file for user {$userId}");
        return $userDataXML;
    }

    public function readShelfReadXML($userId, $index = 1, $arrayXML = array()){
        $pageIndex = $index;
        $fileName = config('goodreads.storage.storage_folder') .
                    $userId .
                    config('goodreads.storage.xml_shelf_read') .
                    $pageIndex .
                    config('goodreads.storage.xml_extension');
        $shelfReadXML = simplexml_load_file(storage_path($fileName)) or die("Error: Cannot read shelfRead file for user {$userId}");
        //guarda o xml no array
        array_push($arrayXML, $shelfReadXML);

        $xmlReviews = $shelfReadXML->reviews;
        $endNumberOfReviews = (int) $xmlReviews['end'];
        $totalNumberOfReviews = (int) $xmlReviews['total'];

        if ($endNumberOfReviews < $totalNumberOfReviews) {
            $pageIndex++;
            $arrayXML = $this->readShelfReadXML($userId, $pageIndex, $arrayXML);
        }

        return $arrayXML;
    }
    public function getUserProfileStatus($userId){
        $userDataXML = $this->readUserInfoXML($userId);

        if (!$this->isIdValid($userDataXML)){
            return config('goodreads.status.profile_not_found');
        }
        if ($this->isPrivate($userDataXML)){
            return config('goodreads.status.profile_private');
        }
        return config('goodreads.status.profile_valid');
    }

    // if node <private> exists then user profile is private
    public function isPrivate($userDataXML){
        return isset($userDataXML->user->private);
    }
    // if node <user> does not exists then user id is not valid
    public function isIdValid($userDataXML){
        return isset($userDataXML->user);
    }

    public function getUserAvatarUrl($userDataXML){
        return $userDataXML->user->image_url;
    }

    public function getUserName($userDataXML){
        return $userDataXML->user->name;
    }

    public function getNumberBooksRead($userDataXML){
        return $userDataXML->user->user_shelves->user_shelf[0]->book_count;
    }

    public function getNumberBooksReading($userDataXML){
        return $userDataXML->user->user_shelves->user_shelf[1]->book_count;
    }

    public function getReviewsArray($arrayXML){
        $reviewsArray = array();
        for ($i=0; $i < sizeof($arrayXML); $i++) {
            if ($arrayXML[$i]->reviews['total'] == 0){
                return config('goodreads.status.profile_no_data');
            }
            $totalBooks = $arrayXML[$i]->reviews['end'] - $arrayXML[$i]->reviews['start'] + 1;
            for ($j=0; $j < $totalBooks; $j++) {
                array_push($reviewsArray, $arrayXML[$i]->reviews->review[$j]);
            }
        }
        return $reviewsArray;
    }

    // creates and returns an array of Book objects, sorted by timeToRead
    public function getAllBooksRead($shelfBooks){
        $readBooksArray = array();
        for ($i=0; $i < sizeof($shelfBooks); $i++) {
            $dateStart = 0;
            $dateFinish = 0;
            //Mon Oct 30 07:12:43 -0700 2017
            // original format: 'D M j H:i:s O Y'
            // goal format: 'M j Y'
            $format = 'D M j H:i:s O Y';
            if ($shelfBooks[$i]->read_at->__toString()) {
                $dateFinish = \DateTime::createFromFormat("D M j H:i:s O Y", $shelfBooks[$i]->read_at);
                $dateFinish = $dateFinish->format('j M Y');
                $datetime2 = strtotime($dateFinish);
            }
            // if a book was simply added as read then started_at will be empty
            else{
                $dateFinish = 0;
                $datetime2 = 0;
            }
            if ($shelfBooks[$i]->started_at->__toString()){
                $dateStart = \DateTime::createFromFormat("D M j H:i:s O Y", $shelfBooks[$i]->started_at);
                $dateStart = $dateStart->format('j M Y');
                $datetime1 = strtotime($dateStart);
            }
            else{
                $dateStart = 0;
                $datetime1 = 0;
            }
            if ($datetime1 == 0 || $datetime2 == 0) {
                $days = 0;
            }
            else {
                $secs = $datetime2 - $datetime1;// == <seconds between the two times>
                $days = $secs / 86400;
            }
            $yearRead = 0;
            if ($shelfBooks[$i]->started_at->__toString()){
                if($dateFinish) {
                    // extract year from date
                    $parts = explode(' ', $dateFinish);
                    $yearRead = $parts[2];
                }
                // sometimes a 'read' book has 'started_at' defined but not 'read_at', even though the book is marked as 'read'.
                // don't know why so I just set 'yearRead' with the year from 'started_at'
                else {
                    $parts = explode(' ', $dateStart);
                    $yearRead = $parts[2];
                }
            }
            //TODO: only add books with dateAdded older than account creation date
            // if 'read_at' not defined
//            if (!$shelfBooks[$i]->read_at->__toString()){
//                $dateAdded = \DateTime::createFromFormat("D M j H:i:s O Y", $shelfBooks[$i]->date_added);
//                $dateAdded = $dateAdded->format('j M Y');
//                $pieces = explode(' ', $dateAdded);
//                $last_piece = array_pop($pieces);
//                $yearRead = $last_piece;
//            }
            $newBook = new Book($shelfBooks[$i]->book->title_without_series,
                $shelfBooks[$i]->book->authors->author->name,
                $shelfBooks[$i]->book->publication_year,
                $shelfBooks[$i]->book->num_pages,
                $dateStart,  // started_at
                $dateFinish, // read_at
                $days,		 // timeToRead
                $yearRead,
                $shelfBooks[$i]->book->average_rating,
                $shelfBooks[$i]->book->ratings_count,
                $shelfBooks[$i]->rating,
                $shelfBooks[$i]->book->link,
                $shelfBooks[$i]->book->small_image_url,
                $shelfBooks[$i]->book->authors->author->link,
                $shelfBooks[$i]->book->authors->author->small_image_url);
            array_push($readBooksArray, $newBook);
        }

        usort($readBooksArray, function($a, $b) {

            if($a->timeToRead >= $b->timeToRead){
                return true;
            }else{
                return false;
            }
        });
        return $readBooksArray;
    }

    // returns the top $howMany fastest books read
    public function getFastestBooksRead($readBooksArray, $howMany = 5){
        $topFastest = array();
        for ($i=0; $i <sizeof($readBooksArray); $i++) {
            if ($readBooksArray[$i]->started_at != 0) {
                for ($j=$i; $j<$i+$howMany; $j++) {
                    if ($j >= sizeof($readBooksArray)) {
                        return $topFastest;
                    }
                    array_push($topFastest, $readBooksArray[$j]);
                }
                return $topFastest;
            }
        }
    }

    // returns the top $howMany slowest books read
    public function getSlowestBooksRead($readBooksArray, $howMany = 5){
        $topSlowest = array();
        $topSize = $howMany;
        if ($howMany >= sizeof($readBooksArray)){
            $topSize = sizeof($readBooksArray);
        }
        for ($i=0; $i < $topSize; $i++) {
            array_push($topSlowest, $readBooksArray[sizeof($readBooksArray) - 1 - $i]);
        }
        return $topSlowest;
    }

    // [currentYear][currentYearCount]
    public function getBooksReadByYear($readBooksArray){
        // sort by year read
        usort($readBooksArray, function($a, $b) {
            if($a->yearRead > $b->yearRead){
                return true;
            }else{
                return false;
            }
        });
        $byYear = array();
        $currentYear = 0;
        $currentYearCount = 0;

        for ($i=0; $i < sizeof($readBooksArray); $i++) {
            //if($readBooksArray[$i]->started_at != 0){
                if ($readBooksArray[$i]->yearRead > $currentYear) {
                    // save year and how many books read that year
                    $nextYear = array($currentYear, $currentYearCount);
                    array_push($byYear, $nextYear);
                    // update current year
                    $currentYear = $readBooksArray[$i]->yearRead;
                    // reset year count
                    $currentYearCount = 1; // TODO: last book is not added so it starts at 1, needs fixing
                } else{
                    $currentYearCount++;
                }
            //}
        }
        $nextYear = array($currentYear, $currentYearCount);
        array_push($byYear, $nextYear);
        //removes first element cause it's a dummy
        array_shift($byYear);
        return $byYear;
    }

    // how many books read in year with most read books
    // [currentYear][currentYearCount]
    public function getMaxReadBooks($booksReadPerYear){

        $maxBooks = 0;

        for ($i=0; $i < sizeof($booksReadPerYear); $i++) {
            if ($booksReadPerYear[$i][1] > $maxBooks) {
                $maxBooks = $booksReadPerYear[$i][1];
            }
        }
        return $maxBooks;
    }

    public function getMeanTimeToRead($readBooksArray){

        $totalTime = 0;
        $totalBooks = 0;

        foreach ($readBooksArray as &$book) {
            if($book->started_at != 0){
                $totalBooks++;
                $totalTime += (int) $book->timeToRead;
            }
        }
        if($totalBooks == 0){
            return 0;
        }
        return round($totalTime / $totalBooks);
    }

    // returns an array with number of books per rating range
    // [0,5][12]
    // [1,0][4]
    // [1,5][23]
    // [2,0][2]
    public function sortByAverageRating($readBooksArray){

        // sort by average rating
        usort($readBooksArray, function($a, $b) {

            if($a->averageRating > $b->averageRating){
                return true;
            }else{
                return false;
            }
        });

        $currentRating = 0;

        $averageRatingBreakdown = array(); //array_fill(0, 10, 0);
        for ($i=0; $i < 10; $i++) {

            $currentRating = $currentRating + 0.5;

            $nextRatingRange = array($currentRating, 0);
            array_push($averageRatingBreakdown, $nextRatingRange);
        }

        for ($i=0; $i < sizeof($readBooksArray); $i++) {

            for ($j=0; $j < sizeof($averageRatingBreakdown); $j++) {


                if ((round($readBooksArray[$i]->averageRating * 2) / 2) == $averageRatingBreakdown[$j][0]) {

                    $averageRatingBreakdown[$j][1]++;
                }
            }
        }
        return $averageRatingBreakdown;
    }

    public function getJoinDate($userDataXML){

        //<joined>08/2012</joined>
        $joined = $userDataXML->user->joined;

        $date = explode('/', $joined);
        $monthNum = (int) $date[0];
        $year = $date[1];

        $dateObj   = \DateTime::createFromFormat('!m', $monthNum);
        $monthName = $dateObj->format('F');

        return $monthName . ' ' . $year;
    }

    public function getAverageRating($readBooksArray){

        $totalBooksRated = 0;
        $ratingSum = 0;

        for ($i=0; $i < sizeof($readBooksArray); $i++) {
            if ($readBooksArray[$i]->userRating != 0) {
                $totalBooksRated++;
                $ratingSum += $readBooksArray[$i]->userRating;
            }
        }
        if ($totalBooksRated == 0) {
            return number_format(0, 2);
        }
        return number_format($ratingSum / $totalBooksRated, 2);
    }

    public function getHighestRated($readBooksArray){

        // sort by average rating
        usort($readBooksArray, function($a, $b) {

            if($a->averageRating > $b->averageRating){
                return true;
            }else{
                return false;
            }
        });
        return $readBooksArray[sizeof($readBooksArray)-1];
    }

    public function getLowestRated($readBooksArray){

        // sort by average rating
        usort($readBooksArray, function($a, $b) {

            if($a->averageRating > $b->averageRating){
                return true;
            }else{
                return false;
            }
        });
        return $readBooksArray[0];
    }

    public function getMostReadAuthors($readBooksArray, $howMany = 1){

        $countArray = array();
        // key - author name
        // value - author books read
        foreach($readBooksArray as $item){
            if(array_key_exists($item->author,  $countArray)){
                // $inc = $countArray[$item->author] + 1;
                $countArray[$item->author]->booksRead ++;
            }else{

                $inc = 1; //keep
                $newAuthor = new Author($item->author,
                    $item->authorURL,
                    $item->authorPhotoURL,
                    $inc);
                $countArray[$item->author] = $newAuthor;
            }
        }
        usort($countArray, function($a, $b) {
            if($a->booksRead < $b->booksRead){
                return true;
            }else{
                return false;
            }
        });
        array_splice($countArray, $howMany);
        return $countArray;
    }

    public function getAveragePages($readBooksArray){

        $totalBooksRead = sizeof($readBooksArray);
        $pageSum = 0;

        for ($i=0; $i < sizeof($readBooksArray); $i++) {
            if ($readBooksArray[$i]->pages != 0) {
                $pageSum += $readBooksArray[$i]->pages;
            }
        }
        return number_format($pageSum / $totalBooksRead, 0);
    }
}
