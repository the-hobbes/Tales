<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

		<title>Parallax Test Page</title>
		<meta name="generator" content="TextMate http://macromates.com/" />
		<meta name="author" content="Phelan Vendeville" />
		<meta name="description" content="Logon page for Tales, final project for CS148." />
	
		<link rel="stylesheet"
	  		href="parallax.css"
	  		type="text/css" />
	
		<link rel="stylesheet"
	 		href="style.css"
	 		type="text/css" />
		
		<link href='http://fonts.googleapis.com/css?family=Belleza' rel='stylesheet' type='text/css'><!--google font embedding-->
		<!-- include jquery, jquery scrollTo, and jquery easing-->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="scripts/jquery.scrollTo-1.4.2-min.js" type="text/javascript" charset="utf-8"></script>
		<script src="http://gsgd.co.uk/sandbox/jquery/easing/jquery.easing.1.3.js" type="text/javascript" charset="utf-8"></script>
		<script src="scripts/parallaxer.js" type="text/javascript" charset="utf-8"></script>
		<script>
			/**
			* panel even sniffer function
			* figures out when to unroll the narrative when we scroll over a panel
			*/
			function panelEventSniffer(){
			        var scroll = $(window).scrollLeft();

			        if( scroll >= -200 && scroll < 200 && p.panelHovered != 1 ){
			                p.panelNarr('#c_1');
			                p.panelHovered = 1;
			        }
			        else if( scroll >= 200 && scroll < 600 && p.panelHovered != 2 ){
			                p.panelNarr('#c_2');
			                p.panelHovered = 2;
			        }
			        else if( scroll >= 600 && scroll < 1000 && p.panelHovered != 3 ){
			                p.panelNarr('#c_3');
			                p.panelHovered = 3;
			        }
			        else if( scroll >= 1000 && scroll < 1400 && p.panelHovered != 4 ){
			                p.panelNarr('#c_4');
			                p.panelHovered = 4;
			        }
			        else if( scroll >= 1400 && scroll < 1800 && p.panelHovered != 5 ){
			                p.panelNarr('#c_5');
			                p.panelHovered = 5;
			        }
			        else if( scroll >= 1800 && scroll < 2200 && p.panelHovered != 6 ){
			                p.panelNarr('#c_6');
			                p.panelHovered = 6;
			        }
			}
			
		</script>
	</head><!--end head -->
	
	<body class="bodyReset">
		<div id="header">
			<div id="title">t &nbsp; &nbsp; a &nbsp; &nbsp; l &nbsp; &nbsp; e &nbsp; &nbsp; s</div><!-- end title-->
		</div><!-- end header -->
			<div id="content">
				
				<ul id="panelControl">
				        <li><a href="#c_1" title="Panel 1"><span>1</span></a></li>
				        <li><a href="#c_2" title="Panel 2"><span>2</span></a></li>
				        <li><a href="#c_3" title="Panel 3"><span>3</span></a></li>
				        <li><a href="#c_4" title="Panel 4"><span>4</span></a></li>
				        <li><a href="#c_5" title="Panel 5"><span>5</span></a></li>
				        <li><a href="#c_6" title="Panel 6"><span>6</span></a></li>
				</ul>
				
				<div id="overflowControl">
				        <div id="layerSling">
						<!-- the number represents the panel, the letter represents the layer -->
				                <div id="layerA"> <!-- the back most layer-->
				                        <div class="p">1a</div>
				                        <div class="p">2a</div>
				                        <div class="p">3a</div>
				                        <div class="p">4a</div>
				                        <div class="p">5a</div>
				                        <div class="p">6a</div>
				                </div>
				                <div id="layerB"><!-- we choose this to be the primary layer -->
				                        <div class="p" id="c_1">1b<div class="narrative"><p>Bacon ipsum dolor sit amet anim jerky sirloin, brisket salami cillum jowl.</p></div></div>
										                        <div class="p" id="c_2">2b<div class="narrative"><p>Laboris occaecat ut dolore minim, non shankle laborum sausage boudin meatball shoulder.</p></div></div>
										                        <div class="p" id="c_3">3b<div class="narrative"><p>Pastrami shankle ad chuck, chicken in strip steak pariatur culpa ex fatback sunt incididunt exercitation elit.</p></div></div>
										                        <div class="p" id="c_4">4b<div class="narrative"><p>Elit dolor labore in pork tempor tri-tip cillum tenderloin duis, eiusmod ut aliquip strip steak.</p></div></div>
										                        <div class="p" id="c_5">5b<div class="narrative"><p>Pancetta swine in dolore id laborum, cupidatat adipisicing mollit.</p></div></div>
										                        <div class="p" id="c_6">6b<div class="narrative"><p>Officia incididunt adipisicing pancetta, ut veniam spare ribs cillum tempor flank chuck ex consectetur.</p></div></div>
				                </div>
				                <div id="layerC">
				                        <div class="p">1c</div>
				                        <div class="p">2c</div>
				                        <div class="p">3c</div>
				                        <div class="p">4c</div>
				                        <div class="p">5c</div>
				                        <div class="p">6c</div>
				                </div>
				                <div id="layerD">
				                        <div class="p">1d</div>
				                        <div class="p">2d</div>
				                        <div class="p">3d</div>
				                        <div class="p">4d</div>
				                        <div class="p">5d</div>
				                        <div class="p">6d</div>
				                </div>
				                <div id="layerE"><!-- the front most layer-->
				                        <div class="p">1e</div>
				                        <div class="p">2e</div>
				                        <div class="p">3e</div>
				                        <div class="p">4e</div>
				                        <div class="p">5e</div>
				                        <div class="p">6e</div>
				                </div>
				        </div><!-- end layerSling -->
				</div><!-- end overflow control -->
				
			</div><!-- end form content-->
	</body><!-- end body -->
</html>