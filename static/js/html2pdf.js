// html2pdf.js
var page = new WebPage();
var system = require("system");
// change the paper size to letter, add some borders
// add a footer callback showing page numbers
page.paperSize = {
  format: "Letter",
  charset:"utf8",
  orientation: "landscape",
  margin: {left:"0cm", right:"0cm", top:"0cm", bottom:"0cm"},
  footer: {
    height: "0.9cm",
    contents: phantom.callback(function(pageNum, numPages) {
      return "<div style='text-align:center;'><small>" + pageNum +
        " / " + numPages + "</small></div>";
    })
  },
  header: {
    height: "0.5cm",
    contents: phantom.callback(function(pageNum, numPages) {
      return "";
    })
  }
};
page.zoomFactor = 1;
// assume the file is local, so we don't handle status errors
page.open(system.args[1], function (status) {
  if (page.evaluate(function(){return typeof PhantomJSPrinting == "object";})) {
    paperSize = page.paperSize;
    paperSize.header.height = page.evaluate(function() {
      return PhantomJSPrinting.header.height;
    });
    paperSize.header.contents = phantom.callback(function(pageNum, numPages) {
      return page.evaluate(function(pageNum, numPages){return PhantomJSPrinting.header.contents(pageNum, numPages);}, pageNum, numPages);
    });
    paperSize.footer.height = page.evaluate(function() {
      return PhantomJSPrinting.footer.height;
    });
    paperSize.footer.contents = phantom.callback(function(pageNum, numPages) {
      return page.evaluate(function(pageNum, numPages){return PhantomJSPrinting.footer.contents(pageNum, numPages);}, pageNum, numPages);
    });
      
    page.paperSize = paperSize;
  }
  page.render(system.args[2]);
  phantom.exit();
});