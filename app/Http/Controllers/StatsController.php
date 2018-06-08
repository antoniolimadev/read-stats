<?php

namespace App\Http\Controllers;
use App\Goodreads\StatsManager;
use App\User;

class StatsController extends Controller
{
    protected $statsManager;
    protected $status;
    protected $serverMessage;
    public function index()
    {
        $userDataArray = 0;
        $serverMessage = 0;
        return view('main.index', compact('userDataArray', 'serverMessage'));
    }

    public function stats($goodreads_id)
    {
        $statsManager = new StatsManager($goodreads_id);
        $userDataArray = 0; // init
        // check if user exists in db
        $user = User::where('goodreads_id', $goodreads_id)->get()->first();
        if (!$user){
            // make api request
            if (!$statsManager->saveUserInfoXML($goodreads_id)){
                // if request went wrong
                $this->status = config('goodreads.status.request_failed');
            } else { // if request was successful
                //add user to db
                User::create([
                    'goodreads_id' => $goodreads_id,
                    'last_access' => new \DateTime()
                ]);
                // check profile status
                $this->status = $statsManager->getUserProfileStatus($goodreads_id);
                if ($this->status == config('goodreads.status.profile_valid')) {
                    // fetch shelf data from curl and save it in storage
                    $statsManager->saveShelfReadXML($goodreads_id);
                }
            }
        // if user already exists in db
        }else{
            $this->status = $statsManager->getUserProfileStatus($goodreads_id);
            $user->updateLastAccess();
        }
        // arrange data according to profile status
        switch($this->status){
            case config('goodreads.status.request_failed'):
                $this->serverMessage = config('goodreads.strings.request_failed');
                break;
            case config('goodreads.status.profile_valid'):
                // fetch shelf data from storage
                $shelfReadXML = $statsManager->readShelfReadXML($goodreads_id);
                // check if books read > 0
                if ($statsManager->getReviewsArray($shelfReadXML) == config('goodreads.status.profile_no_data')){
                    $this->serverMessage = config('goodreads.strings.profile_no_books_read');
                } else {
                    // fetch user data from storage
                    $userDataXML = $statsManager->readUserInfoXML($goodreads_id);
                    // build data array
                    $userDataArray = $this->getUserDataArray($userDataXML, $shelfReadXML, $statsManager);
                }
                break;
            case config('goodreads.status.profile_private'):
                $this->serverMessage = config('goodreads.strings.profile_private_message');
                break;
            case config('goodreads.status.profile_not_found'):
                $this->serverMessage = config('goodreads.strings.profile_not_found_message');
                break;
        }
        // return view with data array
        return view('main.index',['userDataArray' => $userDataArray,
                                        'serverMessage' => $this->serverMessage]);
    }

    public function generate(){
        $userid = request('goodreads_id');
        return redirect('/userstats/' . $userid);
    }

    protected function getUserDataArray($userDataXML, $shelfReadXML, $statsManager){
        // user info
        $userId = $statsManager->getUserId();
        $userName = $statsManager->getUserName($userDataXML);
        $userAvatarUrl = $statsManager->getUserAvatarUrl($userDataXML);
        $joinDate = $statsManager->getJoinDate($userDataXML);
        $booksRead = $statsManager->getNumberBooksRead($userDataXML);
        $curr_reading = $statsManager->getNumberBooksReading($userDataXML);

        // read shelf
        $arrayXML = $statsManager->getReviewsArray($shelfReadXML);		// array with all xml files
        $readBooksArray = $statsManager->getAllBooksRead($arrayXML);	//array of Books sorted by timeToRead
        $averageUserRating = $statsManager->getAverageRating($readBooksArray);
        $averagePages = $statsManager->getAveragePages($readBooksArray);
        $totalBooksRead = sizeof($readBooksArray);
        $meanTime = $statsManager->getMeanTimeToRead($readBooksArray);
        $fastestBooks = $statsManager->getFastestBooksRead($readBooksArray);
        $slowestBooks = $statsManager->getSlowestBooksRead($readBooksArray);
        $highestRatedBook = $statsManager->getHighestRated($readBooksArray);
        $lowestRatedBook = $statsManager->getLowestRated($readBooksArray);
        $authorsFrequency = $statsManager->getMostReadAuthors($readBooksArray, 5);
        $booksReadPerYear = $statsManager->getBooksReadByYear($readBooksArray);
        $maxBooksRead = $statsManager->getMaxReadBooks($booksReadPerYear);
        // graph
        $graphWidth = 620;
        if (sizeof($booksReadPerYear) > 7) {
            $graphWidth = 600 + (sizeof($booksReadPerYear) - 7) * 80;
        }
        // array with the height for each graph bar
        $heightArray = array();
        for ($i=0; $i < sizeof($booksReadPerYear); $i++) {
            array_push($heightArray, number_format((($booksReadPerYear[$i][1] / $maxBooksRead) * 180), 0));
        }
        $maxStringSize = 40;

        return compact('userId','userName', 'userAvatarUrl', 'joinDate', 'booksRead', 'curr_reading',
                        'averageUserRating', 'averagePages', 'totalBooksRead', 'meanTime', 'fastestBooks', 'slowestBooks',
                        'highestRatedBook', 'lowestRatedBook', 'authorsFrequency', 'booksReadPerYear', 'maxBooksRead',
                        'graphWidth', 'heightArray', 'maxStringSize');
    }
}
