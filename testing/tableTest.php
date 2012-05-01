<html>
	<head>
		<link rel="stylesheet" href="tableStyle.css" type="text/css" />
	</head>
	<body>
		<table summary="Table pulled from database">
			<caption>Available Stories</caption>
			
			<thead>
				<tr>
					<th scope="col">Story ID</th>
					<th scope="col">Author Name</th>
					<th scope="col">Story Name</th>
				</tr><!-- End column headers -->
			</thead><!-- end header -->

			<tfoot>
				<tr>
					<!--<th scope="row">Footer</th>-->
					<!--<td colspan="2">Footer Data</td>-->
					<td colspan="3"></td>
				</tr><!-- end column footers -->
			</tfoot><!-- end footer -->

			<tbody>
				<tr class = "odd"><!-- note the ODD class -->
					<td><a href="#">Story ID 1</a></td>
					<td><a href="#">Author Name 1</a></td>
					<td><a href="#">Story Name 1</a></td>
				</tr><!-- end of first row -->

				<tr> 
					<td><a href="#">Story ID 1</a></td>
					<td><a href="#">Author Name 1</a></td>
					<td><a href="#">Story Name 1</a></td>
				</tr><!-- end of second row... -->

				<tr class = "odd">
					<td><a href="#">Story ID 1</a></td>
					<td><a href="#">Author Name 1</a></td>
					<td><a href="#">Story Name 1</a></td>
				</tr><!-- end of third row -->

				<tr>
					<td><a href="#">Story ID 1</a></td>
					<td><a href="#">Author Name 1</a></td>
					<td><a href="#">Story Name 1</a></td>
				</tr><!-- end of fourth row... -->

			</tbody><!-- end table body -->

		</table><!--end table-->
	</body>
</html>