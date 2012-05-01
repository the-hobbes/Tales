<?php
/**
* Class TableRow
* Used to create instances of objects that will represent the rows in the table.
* The constructor requires the row (an array created from the mysql query), and the index of the row in order to style odd and even rows. 
*/
class TableRow
{
    //protected, so subclasses can access them
    protected $storyId = "";
    protected $authorName = "";
    protected $storyName = "";
    protected $hyperlink = "";
    protected $rowNumber = 0; //odd or even row?
    protected $debug = FALSE; //are we debugging?

    protected $row = ""; //the variable containing all row data

    /**
     * default constructor
     * requires the row object obtained from php (as a keyed array)
     */
    public function __construct($passRow, $passRowNumber)
    {
    	//initialize properties of the object instance
    	$this->storyId = $passRow['pk_story_storyid'];
    	$this->authorName = $passRow['fk_storyteller_username'];
    	$this->storyName = $passRow['fld_storyName'];
    	$this->hyperlink = "#";
    	$this->rowNumber = $passRowNumber;

    	if($debug)
	    	$this->testingFunction();
    }

    /**
     * writeData()
     * public function used to first format and then print a table row, using the instance of TableRow class.
     * called from client code. 
     */
    public function writeData()
    {
    	//declare function variables
    	$firstCell = "";
    	$secondCell = "";
    	$thirdCell = "";

    	//open row tag and setup class based on counter remainder (odd or even)
    	if((($this->rowNumber) % 2) == 1)
    		$this->row .= "<tr class = 'odd'>";
    	else
    		$this->row .= "<tr class = 'even'>";

    	//setup cells
    	$this->firstCell = '<td><a href="' . $this->hyperlink . '">' . $this->storyId . '</a></td>';
    	$this->secondCell = '<td><a href="' . $this->hyperlink . '">' . $this->authorName . '</a></td>';
    	$this->thirdCell = '<td><a href="' . $this->hyperlink . '">' . $this->storyName . '</a></td>';

    	//append rows to row variable
    	$this->row .= $this->firstCell;
    	$this->row .= $this->secondCell;
    	$this->row .= $this->thirdCell;

    	//close row tag
    	$this->row .= "</tr>";

    	//return row variable to caller for printing
    	return $this->row;

    	//return $this->rowNumber % 2;
    	//return $this->rowNumber;
    }

    /**
     * setHyperlink()
     * Function used to set the root value of the hyperlink
     * Arguments: the value to set the root of the hyperlink to
     */
    public function setHyperlink($value)
    {
        $this->hyperlink = $value;
    }

    /**
     * testingFunction()
     * function used to test parameters passed into the object on instantiation
     */
    private function testingFunction()
    {
    	//echo "got here";

    	echo $this->storyId . "\n";
    	echo $this->authorName . "\n";
    	echo $this->storyName . "\n";
    	echo $this->hyperlink . "\n";
    	echo $this->rowNumber . "\n";
    }

    /**
     * destructor method
     * called when objects are removed and memory is reclaimed.
     */
    public function __destruct()
    {
    	if($debug)
    		echo "object destroyed";
    }
}	
?>