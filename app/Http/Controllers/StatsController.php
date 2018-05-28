<?php

namespace App\Http\Controllers;
use App\Goodreads\StatsManager;
use App\User;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    protected $statsManager;
    public function index()
    {
        $user = 0;
        return view('main.index', compact('user'));
    }

    public function stats($goodreads_id)
    {
        $statsManager = new StatsManager($goodreads_id);
        $user = User::where('goodreads_id', $goodreads_id)->get()->first();
        if (!$user){
            // fetch data from curl and save it in storage
            $statsManager->saveUserInfoXML($goodreads_id);
            $statsManager->saveShelfReadXML($goodreads_id);
            //add user to db
            $user = User::create([
                'goodreads_id' => $goodreads_id,
                'last_access' => new \DateTime()
            ]);
        }else{
            $user->updateLastAccess();
        }
        // fetch user data from db
        $userDataXML = $statsManager->readUserInfoXML($goodreads_id);
        if ($statsManager->isPrivate($userDataXML)){
            dd('private');
        }
        if (!$statsManager->isIdValid($userDataXML)){
            dd('not_valid');
        }
        // fetch shelf data from db
        $shelfReadXML = $statsManager->readShelfReadXML($goodreads_id);
        $userDataArray = $this->getUserDataArray($userDataXML, $shelfReadXML, $statsManager);
        return view('main.index', compact('user', 'userDataArray'));
    }

    public function generate(){
        $userid = request('goodreads_id');
        return redirect('/userstats/' . $userid);
    }

    protected function getUserDataArray($userDataXML, $shelfReadXML, $statsManager){
        // user info
        $userName = $statsManager->getUserName($userDataXML);
        $userAvatarUrl = $statsManager->getUserAvatarUrl($userDataXML);
        $joinDate = $statsManager->getJoinDate($userDataXML);
        $booksRead = $statsManager->getNumberBooksRead($userDataXML);
        $curr_reading = $statsManager->getNumberBooksReading($userDataXML);

        // read shelf
        $arrayXML = $statsManager->getReviewsArray($shelfReadXML);		// array with all xml files
        $readBooksArray = $statsManager->getAllBooksRead($arrayXML);	//array of Books sorted by timeToRead32
        $averageUserRating = $statsManager->getAverageRating($readBooksArray);
        $averagePages = $statsManager->getAveragePages($readBooksArray);
        $totalBooksRead = sizeof($readBooksArray);
        $meanTime = $statsManager->getMeanTimeToRead($readBooksArray);
        $fastestBooks = $statsManager->getFastestBooksRead($readBooksArray, 5);
        $slowestBooks = $statsManager->getSlowestBooksRead($readBooksArray, 5);
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
        return array('userName' => $userName,
            'userAvatarUrl' => $userAvatarUrl,
            'joinDate' => $joinDate,
            'booksRead' => $booksRead,
            'curr_reading' => $curr_reading,
            'averageUserRating' => $averageUserRating,
            'averagePages' => $averagePages,
            'totalBooksRead' => $totalBooksRead,
            'meanTime' => $meanTime,
            'fastestBooks' => $fastestBooks,
            'slowestBooks' => $slowestBooks,
            'highestRatedBook' => $highestRatedBook,
            'lowestRatedBook' => $lowestRatedBook,
            'authorsFrequency' => $authorsFrequency,
            'booksReadPerYear' => $booksReadPerYear,
            'maxBooksRead' => $maxBooksRead,
            'graphWidth' => $graphWidth,
            'heightArray' => $heightArray);
    }
}
