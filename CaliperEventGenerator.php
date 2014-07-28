<?php


require_once  '../caliper-php/lib/Caliper.php';
require_once '../caliper-php/lib/Caliper/entities/CaliperDigitalResource.php';
require_once '../caliper-php/lib/Caliper/entities/reading/EPubVolume.php';
require_once '../caliper-php/lib/Caliper/entities/reading/EPubChapter.php';
require_once '../caliper-php/lib/Caliper/entities/reading/EPubSubChapter.php';
require_once '../caliper-php/lib/Caliper/entities/lis/LISCourseSection.php';
require_once '../caliper-php/lib/Caliper/entities/lis/LISOrganization.php';
require_once '../caliper-php/lib/Caliper/entities/lis/LISPerson.php';
require_once '../caliper-php/lib/Caliper/entities/SoftwareApplication.php';
require_once '../caliper-php/lib/Caliper/events/reading/NavigationEvent.php';
require_once '../caliper-php/lib/Caliper/entities/schemadotorg/WebPage.php';



$HOST = "http://localhost:1080/1.0/event/put";
$API_KEY = "FEFNtMyXRZqwAH4svMakTw";
Caliper::init($API_KEY);

// For reference, the current time
$now = 1401216031920;

// ----------------------------------------------------------------
// Step 1: Set up contextual elements
// ----------------------------------------------------------------

// Course context. NOTE - we would want to associate it with a parent

$americanHistoryCourse = new LISCourseSection('https://some-university.edu/politicalScience/2014/american-revolution-101',null);
$americanHistoryCourse->setCourseNumber("AmRev-101");
$americanHistoryCourse->setLabel("American Revolution 101");
$americanHistoryCourse->setSemester("Spring-2014");
$americanHistoryCourse->setLastModifiedAt($now);

$courseWebPage = new WebPage("AmRev-101-landingPage");
$courseWebPage->setName("American Revolution 101 Landing Page");
$courseWebPage->setParentRef($americanHistoryCourse);

// edApp that provides the first reading
$readium = new SoftwareApplication(
		"https://github.com/readium/readium-js-viewer");
$readium->setType("http://purl.imsglobal.org/ctx/caliper/v1/edApp/epub-reader");
$readium->setLastModifiedAt($now);

// edApp that provides the second reading
$courseSmart = new SoftwareApplication(
		"http://www.coursesmart.com/reader");
$courseSmart->setType("http://purl.imsglobal.org/ctx/caliper/v1/edApp/epub-reader");
$courseSmart->setLastModifiedAt($now);

// Student - performs interaction with reading activities
$alice = new LISPerson("https://some-university.edu/students/jones-alice-554433");
$alice->setLastModifiedAt($now);



// ----------------------------------------------------------------
// Step 2: Set up activity context elements (i.e. the two Readings)
// ----------------------------------------------------------------
$readiumReading = new EPubVolume("https://github.com/readium/readium-js-viewer/book/34843#epubcfi(/4/3)");
$readiumReading->setName("The Glorious Cause: The American Revolution, 1763-1789 (Oxford History of the United States)");
$readiumReading->setLastModifiedAt($now);
$readiumReading->setLanguage('English');


$readiumReadingPage1 = new EPubSubChapter("https://github.com/readium/readium-js-viewer/book/34843#epubcfi(/4/3)/1");
$readiumReadingPage1->setName("Key Figures: George Washington)");
$readiumReadingPage1->setLastModifiedAt($now);
$readiumReading->setLanguage("English");
$readiumReadingPage1->setParentRef($readiumReading);

// ----------------------------------------------------------------
// Step 3: Populate Global App State for Event Generator
// ----------------------------------------------------------------

$globalAppState = ['courseWebPage'=>$courseWebPage,
					'currentCourse'=>$americanHistoryCourse,
					'readiumEdApp'=>$readium,
					'readiumReading'=>$readiumReading,
					'readiumReadingpage1'=>$readiumReadingPage1,
					'student'=>$alice
					];


navigateToReading($globalAppState, "readium",$now);
echo ">>>>>> Navigated to Reading provided by Readium... sent NavigateEvent";
viewPageInReading($globalAppState,"readium","1",$now);
echo ">>>>>> Viewed Page with pageId 1 in Readium Reading... sent ViewedEvent";

function  navigateToReading($globalAppState,$edApp,$now) {

	$navEvent = new NavigationEvent();

	// action is set in navEvent constructor... now set agent and object
	$navEvent->setActor( $globalAppState["student"]);
	$navEvent->setObject($globalAppState[$edApp."Reading"]);
	$navEvent->setFromResource($globalAppState["courseWebPage"]);

	// add (learning) context for event
	$navEvent->setEdApp($globalAppState[$edApp."EdApp"]);
	$navEvent->setLisOrganization($globalAppState["currentCourse"]);

	// set time and any event specific properties
	$navEvent->setStartedAt($now);

	// Send event to EventStore
	Caliper::measure($navEvent);

}

function viewPageInReading($globalAppState,$edApp,$pageId,$now) {

	$viewPageEvent = new ViewedEvent();

	// action is set in navEvent constructor... now set actor and object
	$viewPageEvent->setActor($globalAppState["student"]);
	$viewPageEvent->setObject($globalAppState[edApp."ReadingPage".pageId]);

	// add (learning) context for event
	$viewPageEvent->setEdApp($globalAppState[edApp."EdApp"]);
	$viewPageEvent->setLisOrganization($globalAppState["currentCourse"]);

	// set time and any event specific properties
	$viewPageEvent->setStartedAt($now);

	// Send event to EventStore
	Caliper::measure($viewPageEvent);

}

