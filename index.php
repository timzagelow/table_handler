<script src="https://use.fontawesome.com/25bb81ef1d.js"></script>
<style>
	table {
		padding: 8px;
		border: 1px solid gray;
	}
	
	th, td {
		padding: 8px 12px;
	}
	.tabl-sort-icon {
		padding-left: 6px;
	}
	
	.tabl-wrapper table thead th {
		cursor: default;
	}
	
	.tabl-prev-button,
	.tabl-next-button {
		cursor: pointer;
	}
</style>
<div class="tabl-wrapper">
<table class="sorter">
<thead>
	<th data-col-type="int">Column 1</th>
	<th data-col-type="str">Column 2</th>
	<th data-col-type="date">Column 3</th>
	<th data-col-type="str">Column 4</th>
</thead>
<tbody>
	<tr>
		<td>123</td>
		<td>hello</td>
		<td>2014-01-03</td>
		<td>hey</td>
	</tr>
	<tr>
		<td>321</td>
		<td>pff</td>
		<td>2013-02-03</td>
		<td>hey</td>
	</tr>
	<tr>
		<td>432</td>
		<td>ola</td>
		<td>2017-02-03</td>
		<td>hey</td>
	</tr>
</tbody>
</table>
</div>
<script>
function Tablr(table, options) {
	var that = this,
	defaults = {
		sortCol: 0,
		sortDir: 'asc',
		currentPage: 1,
		rowsPerPage: 25,
		previousText: 'Previous',
		nextText: 'Next',
		classes: {
			resultContainer: 'tabl-result-container',
			resultCount: 'tabl-result-count',
			prevButton: 'tabl-prev-button',
			nextButton: 'tabl-next-button',
			sortIcon: 'tabl-sort-icon',
			icons: {
				sortAsc: 'fa fa-caret-up',
				sortDesc: 'fa fa-caret-down'
			},
		}
	};
	
	that.options = that.mergeOptions(defaults, options);
	that.totalRowCount = 0;
	that.paginationContainer = null;
	that.table = table;
	that.init();
}

Tablr.prototype = {
	init: function() {
		this.totalRowCount = this.getTotalRowCount();
		this.addSortHandlers();
		this.initPageState();
		this.initPagination();
		this.addSortHeaderIcon(this.options.sortCol, this.options.sortDir);
		this.showCurrentPage();
	},
	
	showCurrentPage: function(changeState = true) {
		var that = this,
				col = that.options.sortCol;
		
		that.sortRows(col, that.getSortType(col), that.options.sortDir);
		that.showPagination();
		if (changeState) {
			that.changePageState();
		}
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
		that.paginationContainer.className = that.options.classes.resultContainer;
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
		
		resultCounts.className = that.options.classes.resultCount;
		prevButton.className = that.options.classes.prevButton;
		nextButton.className = that.options.classes.nextButton;
		
		resultCounts.innerHTML = resultText;
		prevButton.innerHTML = that.options.previousText;
		nextButton.innerHTML = that.options.nextText;

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
		
		if ((that.options.currentPage * that.options.rowsPerPage) >= that.totalRowCount) {
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
		
		for (var i=0; i < headers.length; i++) {
			// add event listener for click with closure to pass the table
			headers[i].onclick = function(table) {
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
			that.setSortDir(sortCol, newSortDir);
			that.addSortHeaderIcon(sortCol, newSortDir);
			that.sortRows(sortCol, sortBy, newSortDir);
			that.showCurrentPage();
		}
	}, 	
	
	addSortHeaderIcon: function(col, dir) {
		var that = this,
			headers = that.table.querySelectorAll('th'),
			iconContainer = document.createElement('span'),
			icon = document.createElement('i');
		
		iconContainer.className = that.options.classes.sortIcon;
		
		icon.className = dir === 'asc' ? 
			that.options.classes.icons.sortAsc : 
			that.options.classes.icons.sortDesc;
		
		iconContainer.appendChild(icon);
		
		// allows the click handler function for the th to be called
		iconContainer.style.pointerEvents = 'none';
		
		that.clearSortHeaderIcons();
		
		for (var i=0; i < headers.length; i++) {
			if (i === col) {
				headers[i].appendChild(iconContainer);
			}
		}

	},
	
	clearSortHeaderIcons: function() {
		var that = this,
			headers = that.table.querySelectorAll('th');
			
		for (var i=0; i < headers.length; i++) {
			for (var j in headers[i].children) {
				if (headers[i].children[j].className === that.options.classes.sortIcon) {
					headers[i].children[j].parentNode.removeChild(headers[i].children[j]);
				}
			}
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
	
		if (type === 'str') {
			rows.sort(function(a, b) {
				// just reverse a & b if descending
				if (dir == 'desc') {
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

		if (type === 'int') {
			rows.sort(function(a, b) {
				// just reverse a & b if descending
				if (dir == 'desc') {
					var numA = parseInt(b.val), numB = parseInt(a.val);
				} else {
					var numA = parseInt(a.val), numB = parseInt(b.val);
				}

				return (numA - numB);
			});
		}
		
		if (type === 'date') {
			rows.sort(function(a, b) {
				if (dir == 'desc') {
					var dateA = new Date(b.val).getTime(), dateB = new Date(a.val).getTime();
				} else {
					var dateA = new Date(a.val).getTime(), dateB = new Date(b.val).getTime();
				}
				
				return (dateA - dateB);
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
	
	initPageState: function() {
		var that = this;
		
		if (history.state !== null) {
			that.options.sortCol = history.state.sortCol;
			that.options.sortDir = history.state.sortDir;
			that.options.currentPage = history.state.currentPage;
		}
		
		window.onpopstate = function() {
			if (typeof history.state !== 'undefined' && history.state !== null) {
				that.options.sortCol = history.state.sortCol;
				that.options.sortDir = history.state.sortDir;
				that.options.currentPage = history.state.currentPage;
				that.addSortHeaderIcon(that.options.sortCol, that.options.sortDir);
				that.showCurrentPage(false);
			}
		};
	},
	 
	changePageState: function() {
		var that = this,
			tableState = {
				sortCol: that.options.sortCol,
				sortDir: that.options.sortDir,
				currentPage: that.options.currentPage
			};

		history.pushState(tableState, null, null);
	},
	
	mergeOptions: function (obj1, obj2) {
		var obj3 = {};
		for (var attrName in obj1) { obj3[attrName] = obj1[attrName]; }
		for (var attrName in obj2) { obj3[attrName] = obj2[attrName]; }
		return obj3;
	}
};

var tables = document.querySelectorAll('.sorter');

for (var i=0; i < tables.length; i++) {
	var defaults = {
		rowsPerPage: 2
	};
	var myTable = new Tablr(tables[i], defaults);
}

</script>