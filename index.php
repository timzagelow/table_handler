<style>
	table {
		padding: 8px;
		border: 1px solid gray;
	}
	
	th, td {
		padding: 8px 12px;
	}
</style>

<table class="sorter" data-rows-per-page="2">
<thead>
	<th data-col-type="int" data-dir="asc"">Column 1</th>
	<th data-col-type="str">Column 2</th>
	<th data-col-type="date">Column 3</th>
</thead>
<tbody>
	<tr>
		<td>123</td>
		<td>hello</td>
		<td>2014-01-03</td>
	</tr>
	<tr>
		<td>321</td>
		<td>pff</td>
		<td>2013-02-03</td>
	</tr>
	<tr>
		<td>432</td>
		<td>ola</td>
		<td>2017-02-03</td>
	</tr>
</tbody>
</table>

<script>
function tableHandler(table, options) {
	var that = this,
	defaults = {
		totalRowCount: 0,
		rowsPerPage: 25,
		currentPage: 1,
		sortCol: 0
	};
	
	that.options = that.mergeOptions(defaults, options);
	that.table = table;
	that.init();
}

tableHandler.prototype = {
	init: function() {
		this.totalRowCount = this.getTotalRowCount();
		this.addSortHandlers();
		this.showCurrentPage();
	},
	
	showCurrentPage: function() {
		var that = this,
				tbody = that.table.tBodies[0], 
				rows = tbody.rows,
				start = (that.options.currentPage - 1) * that.options.rowsPerPage,
				end = start + (that.options.rowsPerPage - 1);
		
		for (var i=0; i < rows.length; i++) {
			if (i < start || i > end) {
				// rows[i].style.visibility = 'hidden';
				// rows[i].style.height = '0px';
			} else {
				// rows[i].style.display = 'table-row';
			}
		}
	},
	
	addSortHandlers: function() {
		var that = this;
		var headers = that.table.querySelectorAll('th');
		console.log(headers);
		
		for (var j=0; j < headers.length; j++) {
			// add event listener for click with closure to pass the table
			headers[j].onclick = function(table) {
   			return function(event) {
        	that.handleSortHeaderClick(event, table);
    		}
			}(this.table);
		}
	},

	handleSortHeaderClick: function(e, table) {
		var that = this,
				sortBy = e.target.getAttribute('data-col-type'),
				sortCol = e.target.cellIndex;
				newSortDir = that.getNewSortDir(sortCol);
		
		if (typeof sortBy !== 'undefined') {	
			that.sortRows(sortCol, sortBy, newSortDir);
			that.setSortDir(sortCol, newSortDir);
		}
		
	}, 	
	
	getNewSortDir: function(col) {
		var that = this,
				ths = that.table.getElementsByTagName('th');
		
		if (that.options.sortCol !== col) {
			return 'asc';
		}
		
		var dir = ths[that.options.sortCol].getAttribute('data-dir');
		
		if (dir === null) {
			return 'asc';
		} else if (dir === 'desc') {
			return 'asc';
		} else {
			return 'desc';
		}
	},

	setSortDir: function(col, dir) {
			var that = this,
					ths = that.table.getElementsByTagName('th');

			// remove 'sort-col' class from all THs		
			for (var i=0; i < ths.length; i++) {
				ths[i].removeAttribute('data-dir');
			
				if (i === col) {
					that.sortCol = i;
					ths[i].setAttribute('data-dir', dir);
				}
			}
			
			that.options.sortCol = col;
	},
	
	sortRows: function(index, type, dir) {
		var that = this,
				tbody = that.table.tBodies[0],
				rows = [],
				value;
	
		for (i=0; i < tbody.rows.length; i++) {
			value = tbody.rows[i].cells[index].innerHTML;
			rows.push({
				"row": tbody.rows[i], 
				"val": value 
			});
		}
	
		if (type === "str") {
			rows.sort(function(a, b) {
				// just reverse a & b if descending
				if(dir == "desc") {
					var txtA = b.val.toLowerCase(), txtB = a.val.toLowerCase();
				} else {
					var txtA = a.val.toLowerCase(), txtB = b.val.toLowerCase();
				}
		
				if(txtA < txtB)
					return -1;
				if(txtA > txtB)
					return 1;
				return 0;
			});
		}

		if (type === "int") {
			rows.sort(function(a, b) {
				// just reverse a & b if descending
				if(dir == "desc") {
					var numA = parseInt(b.val), numB = parseInt(a.val);
				} else {
					var numA = parseInt(a.val), numB = parseInt(b.val);
				}

				return (numA - numB);
			});
		}
		
		while(tbody.rows.length > 0) {
			tbody.deleteRow(0);
		}
	
		for (var i in rows) {
			tbody.appendChild(rows[i].row);
		}	
	},
	
	
	getTotalRowCount: function() {
			var that = this,
					tbody = that.table.tBodies[0];
		
			return tbody.rows.length;
	},
	
	mergeOptions: function (obj1, obj2) {
		var obj3 = {};
		for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
		for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
		return obj3;
	}
};

var tables = document.querySelectorAll('.sorter');

for (var i=0; i < tables.length; i++) {
	var defaults = {
		rowsPerPage: 2
	};
	var myTable = new tableHandler(tables[i], defaults);
	console.log(myTable);
}

</script>