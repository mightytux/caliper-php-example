<?php
/*
 * 
 */

require_once  '../caliper-php/lib/Caliper.php';
require_once '../caliper-php/lib/Caliper/entities/CaliperDigitalResource.php';
require_once '../caliper-php/lib/Caliper/entities/reading/EPubVolume.php';
require_once '../caliper-php/lib/Caliper/entities/reading/EPubChapter.php';
require_once '../caliper-php/lib/Caliper/entities/reading/EPubSubChapter.php';
require_once '../caliper-php/lib/Caliper/entities/lis/LISCourseSection.php';
require_once '../caliper-php/lib/Caliper/entities/lis/LISOrganization.php';
require_once '../caliper-php/lib/Caliper/entities/lis/LISPerson.php';
require_once '../caliper-php/lib/Caliper/events/annotation/AnnotationEvent.php';
require_once '../caliper-php/lib/Caliper/entities/annotation/HighlightedAnnotation.php';
require_once '../caliper-php/lib/Caliper/entities/annotation/TagAnnotation.php';
require_once '../caliper-php/lib/Caliper/entities/annotation/SharedAnnotation.php';
require_once '../caliper-php/lib/Caliper/entities/annotation/BookmarkAnnotation.php';
require_once '../caliper-php/lib/Caliper/entities/SoftwareApplication.php';
require_once '../caliper-php/lib/Caliper/events/reading/NavigationEvent.php';
require_once '../caliper-php/lib/Caliper/events/reading/ViewedEvent.php';
require_once '../caliper-php/lib/Caliper/entities/schemadotorg/WebPage.php';
require_once '../caliper-php/lib/Caliper/entities/annotation/TextPositionSelector.php';



$HOST = "http://localhost:1080/1.0/event/put";
$API_KEY = "FEFNtMyXRZqwAH4svMakTw";
Caliper::init($API_KEY);

// For reference, the current time
$now = 1401216031920;

echo "=========================================================================<br/>";
echo "Caliper Event Generator: Generating Reading and Annotation Sequence<br/>";
echo"=========================================================================<br/>";

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
$readiumReadingPage1->setLastModifiedAt(time());
$readiumReading->setLanguage("English");
$readiumReadingPage1->setParentRef($readiumReading);

$readiumReadingPage2 = new EPubSubChapter("https://github.com/readium/readium-js-viewer/book/34843#epubcfi(/4/3)/2");
$readiumReadingPage2->setName("Key Figures: Lord Cornwalis)");
$readiumReadingPage2->setLastModifiedAt(time());
$readiumReading->setLanguage("English");
$readiumReadingPage2->setParentRef($readiumReading);

$readiumReadingPage3 = new EPubSubChapter("https://github.com/readium/readium-js-viewer/book/34843#epubcfi(/4/3)/3");
$readiumReadingPage3->setName("Key Figures: Paul Revere)");
$readiumReadingPage3->setLastModifiedAt(time());
$readiumReading->setLanguage("English");
$readiumReadingPage3->setParentRef($readiumReading);

// ........................................................................

$courseSmartReading = new EPubVolume("http://www.coursesmart.com/the-american-revolution-a-concise-history/robert-j-allison/dp/9780199347322");
$courseSmartReading->setName("The American Revolution: A Concise History | 978-0-19-531295-9");
$courseSmartReading->setLastModifiedAt(time());
$courseSmartReading->setLanguage("English");

$courseSmartReadingPageaXfsadf12 = new EPubSubChapter("http://www.coursesmart.com/the-american-revolution-a-concise-history/robert-j-allison/dp/9780199347322/aXfsadf12");
$courseSmartReadingPageaXfsadf12->setName("The Boston Tea Party");
$courseSmartReading->setLastModifiedAt(time());
$courseSmartReadingPageaXfsadf12->setLanguage("English");
$courseSmartReadingPageaXfsadf12->setParentRef($courseSmartReading);

echo ">> generated activity context data<br/>";

// ----------------------------------------------------------------
// Step 3: Populate Global App State for Event Generator
// ----------------------------------------------------------------

$globalAppState = ['courseWebPage'=>$courseWebPage,
					'currentCourse'=>$americanHistoryCourse,
					'readiumEdApp'=>$readium,
					'readiumReading'=>$readiumReading,
					'readiumReadingPage1'=>$readiumReadingPage1,
					'readiumReadingPage2'=>$readiumReadingPage2,
					'readiumReadingPage3'=>$readiumReadingPage3,
					'coursesmartEdApp'=>$courseSmart,
					'coursesmartReading'=>$courseSmartReading,
					'coursesmartReadingPageaXfsadf12'=>$courseSmartReadingPageaXfsadf12,
					'student'=>$alice
					];


echo ">> populated Event Generator's global state<br/>";

// ----------------------------------------------------------------
// Step 4: Execute reading sequence
// ----------------------------------------------------------------
echo ">> sending events<br/>";

// Event # 1 - NavigationEvent
echo "<br/>>>>>>> Navigated to Reading provided by Readium... sent NavigateEvent<br/>";
navigateToReading($globalAppState, "readium");


// Event # 2 - ViewedEvent
echo "<br/>>>>>>> Viewed Page with pageId 1 in Readium Reading... sent ViewedEvent<br/>";
viewPageInReading($globalAppState,"readium","1");

// Event # 3 - ViewedEvent
echo "<br/>>>>>>> Viewed Page with pageId 2 in Readium Reading... sent ViewedEvent<br/>";
viewPageInReading($globalAppState, "readium", "2");

// Event # 4 - HighlitedEvent
echo "<br/>>>>>>> Hilighted fragment in pageId 2 from index 455 to 489 in Readium Reading... sent HilightedEvent<br/>";
hilightTermsInReading($globalAppState, "readium", "2", 455, 489);

//Event # 5 - Viewed Event
echo "<br/>>>>>>> Viewed Page with pageId 3 in Readium Reading... sent ViewedEvent<br/>";
viewPageInReading($globalAppState, "readium", "3");

// Event # 6 - BookmarkedEvent
echo "<br/>>>>>>> Bookmarked Page with pageId 3 in Readium Reading... sent BookmarkedEvent<br/>";
bookmarkPageInReading($globalAppState, "readium", "3");


// Event # 7 - NavigationEvent
echo "<br/>>>>>>> Navigated to Reading provided by CourseSmart... sent NavigateEvent<br/>";
navigateToReading($globalAppState, "coursesmart");


// Event # 8 - ViewedEvent
echo "<br/>>>>>>> Viewed Page with pageId aXfsadf12 in CourseSmart Reading... sent ViewedEvent<br/>";
viewPageInReading($globalAppState, "coursesmart", "aXfsadf12");

// Event # 9 - TaggedEvent
echo "<br/>>>>>>> Tagged Page with pageId aXfsadf12 with tags [to-read, 1776, shared-with-project-team] in CourseSmart Reading... sent TaggedEvent<br/>";
tagPageInReading($globalAppState, "coursesmart", "aXfsadf12",array("to-read", "1776","shared-with-project-team"));


// Event # 10 - SharedEvent
echo "<br/>>>>>>> Shared Page with pageId aXfsadf12 with students [bob, eve] in CourseSmart Reading... sent SharedEvent<br/>";
sharePageInReading($globalAppState,"coursesmart","aXfsadf12",array("https://some-university.edu/students/smith-bob-554433","https://some-university.edu/students/lam-eve-554433"));


function  navigateToReading($globalAppState,$edApp) {

	$navEvent = new NavigationEvent();

	// action is set in navEvent constructor... now set agent and object
	$navEvent->setActor( $globalAppState["student"]);
	$navEvent->setObject($globalAppState[$edApp."Reading"]);
	$navEvent->setFromResource($globalAppState["courseWebPage"]);

	// add (learning) context for event
	$navEvent->setEdApp($globalAppState[$edApp."EdApp"]);
	$navEvent->setLisOrganization($globalAppState["currentCourse"]);

	// set time and any event specific properties
	$navEvent->setStartedAt(time());

	// Send event to EventStore
	Caliper::measure($navEvent);

}

function viewPageInReading($globalAppState,$edApp,$pageId) {

	$viewPageEvent = new ViewedEvent();

	// action is set in navEvent constructor... now set actor and object
	$viewPageEvent->setActor($globalAppState["student"]);
	$viewPageEvent->setObject($globalAppState[$edApp."ReadingPage".$pageId]);

	// add (learning) context for event
	$viewPageEvent->setEdApp($globalAppState[$edApp."EdApp"]);
	$viewPageEvent->setLisOrganization($globalAppState["currentCourse"]);

	// set time and any event specific properties
	$viewPageEvent->setStartedAt(time());

	// Send event to EventStore
	Caliper::measure($viewPageEvent);

}


function  hilightTermsInReading($globalAppState,$edApp,$pageId,$startIndex,$endIndex) {

	$hilightTermsEvent = AnnotationEvent::forAction("highlighted");

	// action is set in navEvent constructor... now set actor and object
	$hilightTermsEvent->setActor($globalAppState["student"]);
	
	$hilightTermsEvent->setObject($globalAppState[$edApp."ReadingPage".$pageId]);

	// set target of highlight create action
	$hilightTermsEvent->setGenerated(getHighlight($startIndex,$endIndex,"Life, Liberty and the pursuit of Happiness",$globalAppState[$edApp."ReadingPage".$pageId]));

	// add (learning) context for event
	$hilightTermsEvent->setEdApp($globalAppState[$edApp."EdApp"]);
	$hilightTermsEvent->setLisOrganization($globalAppState["currentCourse"]);

	// set time and any event specific properties
	$hilightTermsEvent->setStartedAt(time());
	// Send event to EventStore
	Caliper::measure($hilightTermsEvent);
}

/**
 * @param endIndex
 * @param startIndex
 * @return
 */
function  getHighlight($startIndex, $endIndex,$selectionText,$target) {

	$baseUrl = "https://someEduApp.edu/highlights/";	
	$randomUUID = uniqid(uniqid(), true);	
	$highlightAnnotation = new HighlightAnnotation($baseUrl.$randomUUID);	
	$textPositionSelector = new TextPositionSelector();	
	$textPositionSelector->setStart($startIndex);
	$textPositionSelector->setEnd($endIndex);	
	$highlightAnnotation->setSelection($textPositionSelector);
	$highlightAnnotation->setSelectionText($selectionText);
	$highlightAnnotation->setTarget($target);	
	return $highlightAnnotation;
}

function  bookmarkPageInReading($globalAppState,$edApp,$pageId) {

	$bookmarkPageEvent = AnnotationEvent::forAction("bookmarked");

	// action is set in navEvent constructor... now set actor, object
	$bookmarkPageEvent->setActor($globalAppState["student"]);
	$bookmarkPageEvent->setObject($globalAppState[$edApp."ReadingPage".$pageId]);

	// bookmark create action generates a BookmarkAnnotation
	$bookmarkPageEvent->setGenerated(getBookmark($globalAppState[$edApp."ReadingPage".$pageId]));

	// add (learning) context for event
	$bookmarkPageEvent->setEdApp($globalAppState[$edApp."EdApp"]);
	$bookmarkPageEvent->setLisOrganization($globalAppState["currentCourse"]);

	// set time and any event specific properties
	$bookmarkPageEvent->setStartedAt(time());

	// Send event to EventStore
	Caliper::measure($bookmarkPageEvent);
}

/**
 * 
 * @param mixed $target
 * @return BookmarkAnnotation
 */
function  getBookmark($target) {

	$baseUrl = "https://someEduApp.edu/bookmarks/";
	$randomUUID = uniqid(uniqid(), true);
    $bookmarkAnnotation = new BookmarkAnnotation($baseUrl.$randomUUID);
	$bookmarkAnnotation->setTarget($target);
	return $bookmarkAnnotation;
}

function tagPageInReading($globalAppState,$edApp,$pageId,$tags) {

	$tagPageEvent = AnnotationEvent::forAction("tagged");

	// action is set in navEvent constructor... now set actor and object
	$tagPageEvent->setActor($globalAppState["student"]);
	$tagPageEvent->setObject($globalAppState[$edApp."ReadingPage".$pageId]);

	// tag create action generates a TagAnnotation
	$tagPageEvent->setGenerated(getTag($tags,$globalAppState[$edApp."ReadingPage".$pageId]));

	// add (learning) context for event
	$tagPageEvent->setEdApp($globalAppState[$edApp."EdApp"]);
	$tagPageEvent->setLisOrganization($globalAppState["currentCourse"]);

	// set time and any event specific properties
	$tagPageEvent->setStartedAt(time());

	// Send event to EventStore
	Caliper::measure($tagPageEvent);
}
/**
 * 
 * @param mixed $tags
 * @param mixed $target
 * @return TagAnnotation
 */
function  getTag($tags, $target) {

	$baseUrl = "https://someEduApp.edu/tags/";
	$randomUUID = uniqid(uniqid(), true);
	$tagAnnotation = new TagAnnotation($baseUrl.$randomUUID);
	$tagAnnotation->setTags($tags);
	$tagAnnotation->setTarget($target);
	return $tagAnnotation;
}

function  sharePageInReading($globalAppState,$edApp,$pageId,$sharedWithIds) {

	$sharePageEvent = AnnotationEvent::forAction("shared");

	// action is set in navEvent constructor... now set actor and object
	$sharePageEvent->setActor($globalAppState["student"]);
	$sharePageEvent->setObject($globalAppState[$edApp."ReadingPage".$pageId]);

	// tag create action generates a SharedAnnotation
	$sharePageEvent->setGenerated(getShareAnnotation($sharedWithIds,$globalAppState[$edApp."ReadingPage".$pageId]));

	// add (learning) context for event
	$sharePageEvent->setEdApp($globalAppState[$edApp."EdApp"]);
	$sharePageEvent->setLisOrganization($globalAppState["currentCourse"]);

	// set time and any event specific properties
	$sharePageEvent->setStartedAt(time());

	// Send event to EventStore
	Caliper::measure($sharePageEvent);
}
/**
 * 
 * @param mixed $sharedWithIds
 * @param mixed $target
 * @return SharedAnnotation
 */
function  getShareAnnotation($sharedWithIds,$target) {

	$baseUrl = "https://someBookmarkingApp.edu/shares/";
	$randomUUID = uniqid(uniqid(), true);
	$sharedAnnotation = new SharedAnnotation($baseUrl.$randomUUID);
	$sharedAnnotation->setUsers($sharedWithIds);
	$sharedAnnotation->setTarget($target);
	return $sharedAnnotation;
}


