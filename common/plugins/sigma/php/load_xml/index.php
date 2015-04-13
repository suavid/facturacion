<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" -->
    <html>
    <head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <title>Sigma Grid 2.0 XML Sample</title>


    <link rel="stylesheet" type="text/css" href="../../grid/gt_grid.css" />
    <link rel="stylesheet" type="text/css" href="../../grid/skin/vista/skinstyle.css" />
    <script type="text/javascript" src="../../grid/gt_msg_en.js"></script>
    <script type="text/javascript" src="../../grid/gt_grid_all.js"></script>
    <script type="text/javascript" src="../../grid/flashchart/fusioncharts/FusionCharts.js"></script>
    <script type="text/javascript" src="../../grid/calendar/calendar.js"></script>
    <script type="text/javascript" src="../../grid/calendar/calendar-setup.js"></script>
    <script type="text/javascript" src="../../grid/xml2json.js"></script>

    <script type="text/javascript" >

    var grid_demo_id = "myGrid1" ;


    var dsOption= {

    fields :[
    {name : 'order_no'  },
    {name : 'employee'  },
    {name : 'country'  },
    {name : 'customer'  },
    {name : 'order2005' ,type: 'float' },
    {name : 'order2006' ,type: 'float' },
    {name : 'order2007' ,type: 'float' },
    {name : 'order2008' ,type: 'float' },
    {name : 'delivery_date' ,type:'date'  }

    ],
    recordType : 'object',
    data : []
    }



    var colsOption = [
    {id: 'order_no' , header: "Order No" , width :60  },
    {id: 'employee' , header: "Employee" , width :80  },
    {id: 'country' , header: "Country" , width :70  },
    {id: 'customer' , header: "Customer" , width :80  },
    {id: 'order2005' , header: "2005" , width :60},
    {id: 'order2006' , header: "2006" , width :60},
    {id: 'order2007' , header: "2007" , width :60},
    {id: 'order2008' , header: "2008" , width :60},
    {id: 'delivery_date' , header: "Delivery Date" , width :100}

    ];



    var gridOption={
    id : grid_demo_id,
    loadURL : 'Controller.php',
    width: "700",  //"100%", // 700,
    height: "200",  //"100%", // 330,
    container : 'gridbox', 
    replaceContainer : true, 
    encoding : 'UTF-8', // Sigma.$encoding(), 
    dataset : dsOption ,
    columns : colsOption ,
    pageSize:100,
    toolbarContent : 'reload print',
    loadResponseHandler : function(response,requestParameter){
    var json=xml2json.parser(response.text);
    if(json.root.data.length&&json.root.data.length>1){//root.data is an array
    mygrid.setContent({data:json.root.data, pageInfo:{totalRowNum:json.root.cnt}});
    }else{//root.data is object
    mygrid.setContent({data:[json.root.data], pageInfo:{totalRowNum:json.root.cnt}});
    }
    return true;
    }
    };

    var mygrid=new Sigma.Grid( gridOption );
    Sigma.Util.onLoad(function(){mygrid.render()});




    //////////////////////////////////////////////////////////






    </script>
    </head>
    <body>

    <div id="page-container">

    <div id="header">
    <h1>
    Product - Sigma Grid</h1>
    </div>

    <div id="content">

    <h2>Sigma Grid Sample - Loading From XML</h2>
    Before you try this sample, you need to download xml2json.js and copy it to /grid. Xml2json.js is released under GPL license and can be downloaded at <a href="http://www.thomasfrank.se/downloadableJS/xml2json.js">
    http://www.thomasfrank.se/downloadableJS/xml2json.js</a>. &nbsp;<br />
    <font color=red>Please reload this page after you download xml2json.js.</font>
    <div id="bigbox" style="margin:15px;display:!none;">
    <div id="gridbox" style="border:0px solid #cccccc;background-color:#f3f3f3;padding:5px;height:200px;width:700px;" ></div>
    </div>

    </div>

    <h3>About Sigmasoft</h3>
    <p>
    Sigmasoft Technologies LLC is a software company providing cross-browser javascript GUI components and tools & services involved. Our aim is to make AJAX simple and easy. 
    <br>Sigmasoft also provides end-to-end solutions in web development (Web 2.0, PHP, ASP.NET, ASP, JSP, XML, Flash), application development and IT consulting services. Please send email to sales@sigmawidgets.com for further infomation.
    </p>

    <div id="footer">All contents are (c) Copyright 2005 - 2008, Sigma Software Inc. All rights Reserved</div>
    </div>

    </body>
    </html>