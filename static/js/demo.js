/**
 *  highlightRow and highlight are used to show a visual feedback. If the row has been successfully modified, it will be highlighted in green. Otherwise, in red
 */
function highlightRow(rowId, bgColor, after)
{
    var rowSelector = $("#" + rowId);
    rowSelector.css("background-color", bgColor);
    rowSelector.fadeTo("normal", 0.5, function() {
        rowSelector.fadeTo("fast", 1, function() {
            rowSelector.css("background-color", '');
        });
    });
}

function highlight(div_id, style) {
    highlightRow(div_id, style == "error" ? "#e5afaf" : style == "warning" ? "#ffcc00" : "#8dc70a");
}

/**
 updateCellValue calls the PHP script that will update the database. 
 */
function updateCellValue(editableGrid, rowIndex, columnIndex, oldValue, newValue, row, onResponse)
{
    $.ajax({
        url: '/nymsa/prueba/update',
        type: 'POST',
        dataType: "html",
        data: {
            tablename: editableGrid.name,
            id: editableGrid.getRowId(rowIndex),
            newvalue: editableGrid.getColumnType(columnIndex) == "boolean" ? (newValue ? 1 : 0) : newValue,
            colname: editableGrid.getColumnName(columnIndex),
            coltype: editableGrid.getColumnType(columnIndex)
        },
        success: function(response)
        {
            // reset old value if failed then highlight row
            var success = onResponse ? onResponse(response) : (response == "ok" || !isNaN(parseInt(response))); // by default, a sucessfull reponse can be "ok" or a database id 
            if (!success)
                editableGrid.setValueAt(rowIndex, columnIndex, oldValue);
            highlight(row.id, success ? "ok" : "error");
        },
        error: function(XMLHttpRequest, textStatus, exception) {
            alert("Ajax failure\n" + errortext);
        },
        async: true
    });

}



function DatabaseGrid()
{
    this.editableGrid = new EditableGrid("prueba", {
        enableSort: true,
        editmode: "absolute",
        pageSize: 10,
        tableLoaded: function() {
            datagrid.initializeGrid(this);
        },
        modelChanged: function(rowIndex, columnIndex, oldValue, newValue, row) {
            updateCellValue(this, rowIndex, columnIndex, oldValue, newValue, row);
        }
    });
    this.fetchGrid();
    this.updatePaginator();
}

DatabaseGrid.prototype.fetchGrid = function() {
    // call a PHP script to get the data
    this.editableGrid.loadXML("/nymsa/prueba/loaddata");
};

DatabaseGrid.prototype.updatePaginator = function() {
    var paginator = $("#paginator").empty();
    var nbPages = this.editableGrid.getPageCount();
    // get interval
    var interval = this.editableGrid.getSlidingPageInterval(20);
    if (interval == null)
        return;
    // get pages in interval (with links except for the current page)
    var pages = this.editableGrid.getPagesInInterval(interval, function(pageIndex, isCurrent) {
        if (isCurrent)
            return "" + (pageIndex + 1);
        return $("<a>").css("cursor", "pointer").html(pageIndex + 1).click(function(event) {
            editableGrid.setPageIndex(parseInt($(this).html()) - 1);
        });
    });

    // "first" link
    var link = $("<a>").html("<img src='" + image("gofirst.png") + "'/>&nbsp;");
    if (!this.editableGrid.canGoBack())
        link.css({opacity: 0.4, filter: "alpha(opacity=40)"});
    else
        link.css("cursor", "pointer").click(function(event) {
            editableGrid.firstPage();
        });
    paginator.append(link);

    // "prev" link
    link = $("<a>").html("<img src='" + image("prev.png") + "'/>&nbsp;");
    if (!this.editableGrid.canGoBack())
        link.css({opacity: 0.4, filter: "alpha(opacity=40)"});
    else
        link.css("cursor", "pointer").click(function(event) {
            editableGrid.prevPage();
        });
    paginator.append(link);

    // pages
    for (p = 0; p < pages.length; p++)
        paginator.append(pages[p]).append(" | ");

    // "next" link
    link = $("<a>").html("<img src='" + image("next.png") + "'/>&nbsp;");
    if (!this.editableGrid.canGoForward())
        link.css({opacity: 0.4, filter: "alpha(opacity=40)"});
    else
        link.css("cursor", "pointer").click(function(event) {
            editableGrid.nextPage();
        });
    paginator.append(link);

    // "last" link
    link = $("<a>").html("<img src='" + image("golast.png") + "'/>&nbsp;");
    if (!this.editableGrid.canGoForward())
        link.css({opacity: 0.4, filter: "alpha(opacity=40)"});
    else
        link.css("cursor", "pointer").click(function(event) {
            editableGrid.lastPage();
        });
    paginator.append(link);
}

DatabaseGrid.prototype.initializeGrid = function(grid) {
    grid.renderGrid("tablecontent", "testgrid");
};

// helper function to display a message
function displayMessage(text, style) {
    _$("message").innerHTML = "<p class='" + (style || "ok") + "'>" + text + "</p>";
}

EditableGrid.prototype.updatePaginator = function()
{

};







