<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" -->
    <html>
    <head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <title>Sigma Grid 2.0 CRUD Sample</title>


    <link rel="stylesheet" type="text/css" href="../../grid/gt_grid.css" />
    <link rel="stylesheet" type="text/css" href="../../grid/skin/vista/skinstyle.css" />
    <script type="text/javascript" src="../../grid/gt_msg_en.js"></script>
    <script type="text/javascript" src="../../grid/gt_grid_all.js"></script>
    <script type="text/javascript" src="../../grid/flashchart/fusioncharts/FusionCharts.js"></script>
    <script type="text/javascript" src="../../grid/calendar/calendar.js"></script>
    <script type="text/javascript" src="../../grid/calendar/calendar-setup.js"></script>


    <script type="text/javascript" >

    var grid_demo_id = "myGrid1";


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
    recordType : 'object'
    }

    var colsOption = [
    {id: 'order_no' , header: "Order No" , width :60 },
    {id: 'employee' , header: "Employee" , width :80 , editor:{type:'text'}},
    {id: 'country' , header: "Country" , width :70, width :80 , editor : { type :"select" ,options : {'US': 'US' ,'FR':'FR', 'BR':'BR'} ,defaultText : 'US' } },
    {id: 'customer' , header: "Customer" , width :80, width :80 , editor:{type:'text'}},
    {id: 'order2005' , header: "2005" , width :60, width :80 , editor: { type :"text" ,validRule : ['R','F'] }},
    {id: 'order2006' , header: "2006" , width :60, width :80 , editor: { type :"text" ,validRule : ['R','F'] }},
    {id: 'order2007' , header: "2007" , width :60, width :80 , editor: { type :"text" ,validRule : ['R','F'] }},
    {id: 'order2008' , header: "2008" , width :60, width :80 , editor: { type :"text" ,validRule : ['R','F'] }},
    {id: 'delivery_date' , header: "Delivery Date" , width :100, editor: { type :"date" }}

    ];


    var gridOption={
    id : grid_demo_id,
    loadURL : 'Controller.php',
    saveURL : 'Controller.php',
    width: "700",  //"100%", // 700,
    height: "200",  //"100%", // 330,
    container : 'gridbox', 
    replaceContainer : true,
    encoding : 'UTF-8', // Sigma.$encoding(), 
    dataset : dsOption ,
    columns : colsOption ,
    clickStartEdit : true ,
    defaultRecord : {'order_no':"00",'employee':"",'country':"",'customer':"",'order2005':0,'order2006':0,'order2007':0,'order2008':0,'delivery_date':"2008-01-01"},
    pageSize:100,
    toolbarContent : 'reload | add del save | print'
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

    <h2>Sigma Grid CRUD Sample</h2>

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