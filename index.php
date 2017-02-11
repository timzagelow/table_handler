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
		sortCol: 0,
		sortDir: 'asc',
		currentPage: 1,
		rowsPerPage: 25
	};
	
	that.options = that.mergeOptions(defaults, options);
	that.totalRowCount = 0;
	that.paginationContainer = null;
	that.table = table;
	that.init();
}

tableHandler.prototype = {
	init: function() {
		this.totalRowCount = this.getTotalRowCount();
		this.addSortHandlers();
		this.initPagination();
		this.showCurrentPage();
	},
	
	showCurrentPage: function() {
		var that = this,
				col = that.options.sortCol;
		
		that.sortRows(col, that.getSortType(col), that.options.sortDir);
		that.showPagination();
	},
	
	getSortType: function(col) {
		var that = this,
				headers = that.table.querySelectorAll('th'),
				type = 'str';
		
		for (var i=0; i < headers.length; i++) {
			if (col === i) {
				 type = headers[i].getAttribute('data-col-type');
			}
		}
		
		return type;
	},
	
	initPagination: function() {
		var that = this;
	
		that.paginationContainer = document.createElement('div');
		that.paginationContainer.className = 'pagination-container';
		that.table.parentNode.insertBefore(that.paginationContainer, that.table.nextSibling);
	},
	
	getPagination: function() {
		var that = this,
				start = (that.options.currentPage - 1) * that.options.rowsPerPage,
				end = start + (that.options.rowsPerPage - 1),
				numPages = Math.ceil(that.totalRowCount / that.options.rowsPerPage);
	
		return {
			start: start,
			end: end,
			pages: numPages
		};
	},
	
	showPagination: function() {
		var that = this,
				p = that.getPagination(),
				prevButton = document.createElement('a'),
				nextButton = document.createElement('a'),
				resultCounts = document.createElement('span'),
				resultText,
				endCount;
				
		if ((p.end + 1) > that.totalRowCount) {
			endCount = that.totalRowCount;
		} else {
			endCount = p.end + 1;
		}
		
		resultText = (p.start + 1) + " &ndash; " + endCount;
		resultText += " of " + that.totalRowCount + " results found";
		
		resultCounts.innerHTML = resultText;
		prevButton.innerHTML = 'Previous';
		nextButton.innerHTML = 'Next';

		prevButton.onclick = function() {
			return function(e) {
				that.prevPage(e);
			}
		}();
	
		nextButton.onclick = function() {
			return function(e) {
				that.nextPage(e);
			}
		}();
		
		prevButton.style.visibility = that.options.currentPage === 1 ? 'hidden' : 'visible';
		
		if ((that.options.currentPage * that.options.rowsPerPage) > that.totalRowCount) {
			nextButton.style.visibility = 'hidden';
		} else {
			nextButton.style.visibility = 'visible';
		}
		
		// remove all children in the pagination container
		while (that.paginationContainer.firstChild) {
			that.paginationContainer.removeChild(that.paginationContainer.firstChild);
		}
		
		that.paginationContainer.appendChild(prevButton);
		that.paginationContainer.appendChild(resultCounts);	
		that.paginationContainer.appendChild(nextButton);	
	},
	
	prevPage: function() {
		var that = this;
		that.options.currentPage -= 1;
		that.showCurrentPage();
	},

	nextPage: function() {
		var that = this;
		that.options.currentPage += 1;
		that.showCurrentPage();
	},
		
	addSortHandlers: function() {
		var that = this;
		var headers = that.table.querySelectorAll('th');
		
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
			that.options.currentPage = 1;	
			that.sortRows(sortCol, sortBy, newSortDir);
			that.setSortDir(sortCol, newSortDir);
			that.showCurrentPage();
		}
		
	}, 	
	
	getNewSortDir: function(col) {
		var that = this,
				ths = that.table.getElementsByTagName('th');
		
		if (that.options.sortCol !== col) {
			that.options.sortDir = 'asc';
		} else if (that.options.sortDir === 'desc') {
			that.options.sortDir = 'asc';
		} else if (that.options.sortDir === 'asc') {
			that.options.sortDir = 'desc';
		}
		
		return that.options.sortDir;
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
	
		var pagination = that.getPagination();
		
		for (var i in rows) {
			if (i < pagination.start || i > pagination.end) {
				rows[i].row.style.display = 'none';
			}  else {
				rows[i].row.style.display = 'table-row';
			}
			
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
}

</script>