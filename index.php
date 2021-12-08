<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">

    <!-- Import Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <!-- Import FontAwesome CSS -->
    <link href="fontawesome/css/all.min.css" rel="stylesheet">
    <!-- Import Style CSS -->
    <link href="css/style.css" rel="stylesheet">

    <!-- Title tag -->
    <title>DWH-Tool: Dimension Historization</title>

    <style>
        #InputField {background: #fff; border: 1px solid #181618; font-size: 16px; padding: 15px; width: 100%;}

        #myUL {list-style-type: none; padding: 0; margin: 0;}

        #myUL li a {
            border: 1px solid #ccc;
            margin-top: -1px; /* Prevent double borders */
            background-color: #F3F3F3;
            padding-bottom: 10px;
            padding-left: 15px;
            padding-top: 10px;
            text-decoration: none;
            font-size: 16px;
            color: black;
            display: block
        }
        #myUL li a:hover:not(.header) {background-color: #eee;}
        #myUL {border: 1px solid #181618; height:300px}
        #myUL {overflow:hidden; overflow-y:scroll;}
    </style>
</head>
<body class="lead">
<!-- IMPORT HEADER -->
<?php include ('header.php')?>
<!-- DB CONNECTION -->
<?php include('dbconnection.php'); ?>

<div class="container-fluid">
    <div class="col-12">

        <div class="col-12 bg-textbox mb-3 p-3 shadow-sm" style="background: #181618">
            <div class="row">

                <!-- INTRO: JUMBOTRON -->
                <div class="col-md-8 col-12">
                    <h1>Create a historized Dimension</h1>
                    <p>Dimension historization tool to create Transact-SQL procedures based on choosen column names and special keys. Use checkbox to integrate parameters into <a href="https://docs.microsoft.com/en-us/sql/t-sql/functions/hashbytes-transact-sql" target="_blank" hreflang="en" rel="noopener" title="Microsoft Docs: Transact-SQL HASHBYTES">SHA2_256 hash function <i class="fas fa-external-link-alt"></i></a>.</p>
                </div>

                <!-- STATISTICS: DIMENSION KEYS IN TOTAL -->
                <!-- QUERY: SELECT COUNT(DISTINCT `TableKey`) AS counter FROM `Dimension.dTable` -->
                <div class="col-md-2 col-6 text-center">

                    <?php
                    $countall = "SELECT COUNT(DISTINCT `TableKey`) AS counter FROM `Dimension.dTable`";
                    $resultcountall = mysqli_query($conn , $countall);

                    if (mysqli_num_rows($resultcountall) == 1) {
                        while ($row = mysqli_fetch_assoc($resultcountall)) {

                            echo "<p><span style='color: #fff; font-size: 36px; font-weight: bold'>$row[counter]</span><br>Dimensions<br>in Database</p>";
                            usleep(2000);
                        }
                    }
                    ?>

                </div>

                <!-- STATISTICS: COLUMN KEYS IN TOTAL -->
                <!-- QUERY: SELECT COUNT(DISTINCT `ColumnKey`) AS counter FROM `Dimension.dTable` -->
                <div class="col-md-2 col-6 text-center">
                    <?php
                    $countall = "SELECT COUNT(DISTINCT `TableKey`) AS counter, CASE WHEN COUNT(DISTINCT `TableKey`) > 0 THEN '<i class=\"far fa-check-circle\"></i>' ELSE '<i class=\"far fa-times-circle\"></i>' END AS activeConnection FROM `Dimension.dTable`";
                    $resultcountall = mysqli_query($conn , $countall);

                    if (mysqli_num_rows($resultcountall) == 1) {
                        while ($row = mysqli_fetch_assoc($resultcountall)) {

                            echo "<p><span style='color: greenyellow; font-size: 36px; font-weight: bold'>$row[activeConnection]</span><br>Database<br>connection</p>";
                            usleep(2000);
                        }
                    }
                    ?>


                </div>

            </div>
        </div>
    </div>
</div>

<hr class="mt-0 shadow-sm" style="color: #181618; height: 1px; opacity: 0.5">


<!-- JS DIMENSION FILTER -->
<div class="container-fluid">
    <div class="col-12">

        <!-- (1) Input field to insert DIMENSION NAME or pick existing dimension -->
        <div class="col-12 mb-3 p-3 py-4 bg-textbox shadow-sm">
            <div class="row">

                <div class="col-md-2 col-12 py-3">
                    <span style="color: #fff">Choose dimension</span>
                </div>

                <div class="col-md-10 col-12">

                    <!-- CREATE DIMENSION LIST -->
                    <form action="" class="w-100">
                        <input type="text" id="searchFilter" name="searchFilter" placeholder=" Search" onkeyup="filterItems(this);" class="w-100 py-3 mb-3">

                        <select id="dimensionlist" name="dimensionlist" onchange="showDimension(this.value)" size="4" class="w-100">
                            <?php
                            $query = "SELECT DISTINCT `TableKey` AS dimension, COUNT(DISTINCT `ColumnKey`) AS totalColumns FROM `Dimension.dTable` GROUP BY `TableKey` ORDER BY `TableKey`";
                            $resultcountall = mysqli_query($conn , $query);

                            if (mysqli_num_rows($resultcountall) > 0) {
                                while ($row = mysqli_fetch_assoc($resultcountall)) {

                                    echo "<option class='py-3' style='background-color: #F3F3F3; border: 1px solid #ccc; color: #ccc; padding: 10px; font-size: 16px; color: black;' value='$row[dimension]'>$row[dimension] ($row[totalColumns] columns)</option>";
                                    usleep(2000);
                                }
                            }
                            ?>
                        </select>
                    </form>

                </div>
            </div>
        </div>

        <div id="txtHint"></div>

    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="js/bootstrap.bundle.min.js"></script>

<!-- GENERATE SOURCE CODE JS -->
<script type="text/javascript">

    function generateCode() {

        /*  GET VALUES
        ------------------
       (1) Get every input field with class "insertDimensionName"
       Insert chosen dimension name from user input
       ------------------ */
        for (let i = 0; i < document.getElementsByClassName('insertDimensionName').length; i++) {
            document.getElementsByClassName('insertDimensionName')[i].innerHTML = document.getElementById('dimensionNameColumn').value;
        }

        /*  ------------------
        (2) Check which values were selected as main SID und main Key column
        Put value for main SID und main Key column into variables (varMainSID / varMainKey)
        Insert value into every SID and Key class (insertMainSIDColumn / insertMainKeyColumn)
        ------------------ */
        let mainSID = document.getElementById("SelectedMainSID");
        let varMainSID = mainSID.options[mainSID.selectedIndex].value;
        console.log("x Main SID = " + varMainSID);

        let mainKEY = document.getElementById("SelectedMainKEY");
        let varMainKey = mainKEY.options[mainKEY.selectedIndex].value;
        console.log("x Main Key = " + varMainKey);

        // Insert chosen SID column into class insertMainSIDColumn
        for (let i = 0; i < document.getElementsByClassName("insertMainSIDColumn").length; i++) {
            document.getElementsByClassName('insertMainSIDColumn')[i].innerHTML = varMainSID;
        }
        // Insert chosen Key column into class insertMainKeyColumn
        for (let i = 0; i < document.getElementsByClassName("insertMainKeyColumn").length; i++) {
            document.getElementsByClassName('insertMainKeyColumn')[i].innerHTML = varMainKey;
        }

        /*  ------------------
        (3) Create RID based on selected SID
        Check if selected column ends with SID to create RID
        Insert value into every RID class (insertRIDColumn)
        ------------------ */
        if (varMainSID.slice(varMainSID.length - 3) == 'SID') {
            let varMainRID = varMainSID.substr(0, varMainSID.length - 3);
            varMainRID = varMainRID + 'RID';

            for (let i = 0; i < document.getElementsByClassName('insertRIDColumn').length; i++) {
                document.getElementsByClassName('insertRIDColumn')[i].innerHTML = varMainRID;
            }
            console.log('o RID Key: ' + varMainRID);
        } else {
            console.log("ERROR: Main SID column does not end with SID");
        }

        /*  ------------------
        (4) Create array including every column name of the chosen dimension (tempAllColumnsArray)
        Create second array without main SID and main Key column
        Insert values into class insertColumnName
        ------------------ */
        let tempAllColumnsArray = [];
        for (let i = 0; i < document.getElementsByName("columnName").length; i++) {
            tempAllColumnsArray[i] = document.getElementsByName("columnName")[i].value;
            console.log("o Column = " + tempAllColumnsArray[i]);
        }
        // Create new Array without Main SID and Main Key entry
        let allColumnsArray = tempAllColumnsArray.filter(function (f) {
            return f != varMainSID && f != varMainKey
        })

        // Create array with column names with left padding: 90px;
        let columnNameArray = [];
        for (let i = 0; i < allColumnsArray.length; i++) {

            // Case 1: Avoid left padding for first element
            if (i < 1) {
                columnNameArray[i] = "<span>" + allColumnsArray[i] + "," + "<br>" + "</span>";
            }
            // Case 2: Insert element with left padding and line break when not first or last element
            else if (i < allColumnsArray.length - 1) {
                columnNameArray[i] = "<span style='padding-left: 90px'>" + allColumnsArray[i] + "," + "<br>" + "</span>";
            }
            // Case 3: Avoid line break for last element
            else if (i = allColumnsArray.length - 1) {
                columnNameArray[i] = "<span style='padding-left: 90px'>" + allColumnsArray[i] + "," + "</span>";
            }
        }

        // Get every column and insert them into class "insertDimensionName"
        for (let i = 0; i < document.getElementsByClassName("insertColumnName").length; i++) {
            document.getElementsByClassName('insertColumnName')[i].innerHTML = columnNameArray.join('');
        }


        /*  SPECIAL KEYS
        ------------------
        (5) Get Special Key for main SID und main Key column
        ------------------ */
        // Get main SID SpecialKey
        let SIDSpecialKeyValue = document.getElementById("SpecialKey"+varMainSID);
        let SIDSpecialKeyValueIndex = SIDSpecialKeyValue.options[SIDSpecialKeyValue.selectedIndex].text;

        // Get main Key SpecialKey
        let KeySpecialKeyValue = document.getElementById("SpecialKey"+varMainKey);
        let KeySpecialKeyValueIndex = KeySpecialKeyValue.options[KeySpecialKeyValue.selectedIndex].text;

        // Insert main SID SpecialKey into class srcInsertMainSIDSpecialKey
        for (let i = 0; i < document.getElementsByClassName("srcInsertMainSIDSpecialKey").length; i++) {
            document.getElementsByClassName("srcInsertMainSIDSpecialKey")[i].innerHTML = "src."+SIDSpecialKeyValueIndex+",";
        }

        // Insert main SID SpecialKey into class srcInsertMainKeySpecialKey
        for (let i = 0; i < document.getElementsByClassName("srcInsertMainKeySpecialKey").length; i++) {
            document.getElementsByClassName("srcInsertMainKeySpecialKey")[i].innerHTML = "src."+KeySpecialKeyValueIndex+",";
        }

        console.log("X SID Column SpecialKey: "+SIDSpecialKeyValueIndex);
        console.log("X Key Column SpecialKey: "+KeySpecialKeyValueIndex);

        /*
        ------------------
        (6) Get Special Key for other columns beside main SID and main Key
        Create new array with length of all Columns, excluding main SID and main Key,
        and insert selected SpecialKeys for each column
        ------------------ */
        let otherSpecialKeyValue = [];
        let otherSpecialKeyValueIndex = [];

        for (let i = 0; i < allColumnsArray.length; i++){
            otherSpecialKeyValue[i] = document.getElementById("SpecialKey"+allColumnsArray[i]);

            // Case 1: Avoid left padding for first element
            if (i < 1) {
                otherSpecialKeyValueIndex[i] = "<span>src." + otherSpecialKeyValue[i].options[otherSpecialKeyValue[i].selectedIndex].text + "," + "<br>" + "</span>";
            }

            // Case 2: Insert element with left padding and line break when not first or last element
            else if (i < allColumnsArray.length - 1) {
                otherSpecialKeyValueIndex[i] = "<span style='padding-left: 90px'>src." + otherSpecialKeyValue[i].options[otherSpecialKeyValue[i].selectedIndex].text + "," + "<br>" + "</span>";
            }
            // Case 3: Avoid line break for last element
            else if (i = allColumnsArray.length - 1) {
                otherSpecialKeyValueIndex[i] = "<span style='padding-left: 90px'>src." + otherSpecialKeyValue[i].options[otherSpecialKeyValue[i].selectedIndex].text + "," + "</span>";
            }
            console.log("O: "+otherSpecialKeyValueIndex[i]);
        }

        // Get every SpecialKey and insert them into class "srcInsertOtherSpecialKeys" with left padding 90px;
        for (let i = 0; i < document.getElementsByClassName("srcInsertOtherSpecialKeys").length; i++) {
            document.getElementsByClassName('srcInsertOtherSpecialKeys')[i].innerHTML = otherSpecialKeyValueIndex.join('');
        }

        /*
        ------------------
        (7) Create first key schema PlaceholderNameEN = src.SpecialKeyNameEN and PlaceholderNameDE = src.SpecialKeyNameDE
        ------------------ */
        let firstKeySchemaArray = [];
        let firstSchemaSpecialKeyValue = [];

        for (let i = 0; i < allColumnsArray.length; i++) {
            firstSchemaSpecialKeyValue[i] = document.getElementById("SpecialKey"+allColumnsArray[i]);

            // Case 1: Avoid left padding for first element
            if (i < 1) {
                firstKeySchemaArray[i] = "<span>" + allColumnsArray[i] + " = src." +firstSchemaSpecialKeyValue[i].options[firstSchemaSpecialKeyValue[i].selectedIndex].text + ",</span><br>";
            }
            // Case 2: Insert element with left padding and line break when not first or last element
            else if (i < allColumnsArray.length - 1) {
                firstKeySchemaArray[i] = "<span style='padding-left: 90px'>" + allColumnsArray[i] + " = src." + firstSchemaSpecialKeyValue[i].options[firstSchemaSpecialKeyValue[i].selectedIndex].text  + ",</span><br>";
            }
            // Case 3: Avoid line break for last element
            else if (i = allColumnsArray.length - 1) {
                firstKeySchemaArray[i] = "<span style='padding-left: 90px'>" + allColumnsArray[i] + " = src." +firstSchemaSpecialKeyValue[i].options[firstSchemaSpecialKeyValue[i].selectedIndex].text + ",</span>";
            }
        }

        for (let i = 0; i < document.getElementsByClassName("insertFirstKeySchema").length; i++) {
            document.getElementsByClassName('insertFirstKeySchema')[i].innerHTML = firstKeySchemaArray.join('');
        }
        /*
        ------------------
        (8) Create second key schema PlaceholderKey = c.PlaceholderKey; PlaceholderNameEN = c.SpecialKeyNameEN and PlaceholderNameDE = c.SpecialKeyNameDE
        ------------------ */
        let secondKeySchemaArray = [];
        let secondSchemaSpecialKeyValue = [];

        //secondKeySchemaArray[0] = "<span>" + varMainKey + " = c." + varMainKey + ",</span><br>";

        for (let i = 0; i < allColumnsArray.length; i++) {
            secondSchemaSpecialKeyValue[i] = document.getElementById("SpecialKey"+allColumnsArray[i]);

            // Case 1: Avoid left padding for first element
            if (i < 1) {
                secondKeySchemaArray[i] = "<span style='padding-left: 135px'>" + allColumnsArray[i] + " = c." + allColumnsArray[i] + ",</span><br>";
            }

            // Case 2: Insert element with left padding and line break when not first or last element
            else if (i < allColumnsArray.length - 1) {
                secondKeySchemaArray[i] = "<span style='padding-left: 135px'>" + allColumnsArray[i] + " = c." + allColumnsArray[i] + ",</span><br>";
            }

            // Case 3: Avoid line break for last element
            else if (i = allColumnsArray.length - 1) {
                secondKeySchemaArray[i] = "<span style='padding-left: 135px'>" + allColumnsArray[i] + " = c." + allColumnsArray[i] + ",</span>";
            }
        }

        // Add main key as new first element to the created array
        let newSecondKeySchemaArray = ["<span>" + varMainKey + " = c." + varMainKey + ",</span><br>"].concat(secondKeySchemaArray)


        for (let i = 0; i < document.getElementsByClassName("insertSecondKeySchema").length; i++) {
            document.getElementsByClassName('insertSecondKeySchema')[i].innerHTML = newSecondKeySchemaArray.join('');
        }

        /*
        ------------------
        (9) CTE Code
        ------------------ */
        for (let i = 0; i < document.getElementsByClassName('CTEcode').length; i++) {
            document.getElementsByClassName('CTEcode')[i].innerHTML = document.getElementById('cte').value;
        }

        /*
        ------------------
        (10) Generate src.keyMainColumn and src.other columns
        ------------------ */
        for (let i = 0; i < document.getElementsByClassName("srcInsertMainKeyColumn").length; i++) {
            document.getElementsByClassName('srcInsertMainKeyColumn')[i].innerHTML = "src."+varMainKey;
        }

        /*
        ------------------
        (11) Generate src.OtherColumns
        ------------------ */
        let srcColumnNameArray = [];
        for (let i = 0; i < allColumnsArray.length; i++) {

            // Case 1: Avoid left padding for first element
            if (i < 1) {
                srcColumnNameArray[i] = "<span>src." + allColumnsArray[i] + "," + "<br>" + "</span>";
            }
            // Case 2: Insert element with left padding and line break when not first or last element
            else if (i < allColumnsArray.length - 1) {
                srcColumnNameArray[i] = "<span style='padding-left: 90px'>src." + allColumnsArray[i] + "," + "<br>" + "</span>";
            }
            // Case 3: Avoid line break for last element
            else if (i = allColumnsArray.length - 1) {
                srcColumnNameArray[i] = "<span style='padding-left: 90px'>src." + allColumnsArray[i] + "," + "</span>";
            }
        }

        for (let i = 0; i < document.getElementsByClassName("srcInsertColumnName").length; i++) {
            document.getElementsByClassName('srcInsertColumnName')[i].innerHTML = srcColumnNameArray.join('');
        }


        /*  HASH
        ------------------
        (12) When checkbox selected insert column name into RowHash
        Create hash array for column names and hash array for special keys
        ------------------ */
        let hashArray = [];     // HashArray for column names
        let hashSpecialArray = [];  // HashArray for SpecialKeys
        let temp = [];

        for (let i = 0; i < tempAllColumnsArray.length; i++) {
            temp[i] = document.getElementById("SpecialKey"+tempAllColumnsArray[i]);

            if (document.getElementById("Checkbox"+tempAllColumnsArray[i]).checked){

                hashArray[i] = tempAllColumnsArray[i];
                hashSpecialArray[i] = temp[i].options[temp[i].selectedIndex].text;
            }
        }

        var newHashArray = hashArray.filter(function (el) {
            return el != null;
        });
        var newHashSpecialArray = hashSpecialArray.filter(function (el) {
            return el != null;
        });

        for (let i = 0; i < document.getElementsByClassName("insertHash").length; i++) {
           document.getElementsByClassName('insertHash')[i].innerHTML = newHashArray;
        }
        for (let i = 0; i < document.getElementsByClassName("insertSpecialHash").length; i++) {
            document.getElementsByClassName('insertSpecialHash')[i].innerHTML = newHashSpecialArray;
        }
}
</script>

<!-- Copy Code Button-->
<script type="text/javascript">
    function copyFunction() {
        const copyText = document.getElementById("myInput").textContent;
        const textArea = document.createElement('textarea');
        textArea.textContent = copyText;
        document.body.append(textArea);
        textArea.select();
        document.execCommand("copy");
    }
</script>


<!-- GET DB INFOS JS -->
<script type="text/javascript">
    function showDimension(str) {
        var xhttp;
        if (str == "") {
            document.getElementById("txtHint").innerHTML = "";
            return;
        }
        xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("txtHint").innerHTML = this.responseText;
            }
        };
        xhttp.open("GET", "getdimension.php?q="+str, true);
        xhttp.send();
    }
</script>

<!-- SEARCH FILTER JS -->
<script type="text/javascript">
    var optionsCache = [];

    function filterItems(el) {
        var value = el.value.toLowerCase();
        var form = el.form;
        var opt, sel = form.dimensionlist;
        if (value == '') {
            restoreOptions();
        } else {
            // Loop backwards through options as removing them modifies the next
            // to be visited if go forwards
            for (var i=sel.options.length-1; i>=0; i--) {
                opt = sel.options[i];
                if (opt.text.toLowerCase().indexOf(value) == -1){
                    sel.removeChild(opt)
                }
            }
        }
    }

    // Restore select to original state
    function restoreOptions(){
        var sel = document.getElementById('dimensionlist');
        sel.options.length = 0;
        for (var i=0, iLen=optionsCache.length; i<iLen; i++) {
            sel.appendChild(optionsCache[i]);
        }
    }

    window.onload = function() {
        // Load cache
        var sel = document.getElementById('dimensionlist');
        for (var i=0, iLen=sel.options.length; i<iLen; i++) {
            optionsCache.push(sel.options[i]);
        }
    }
</script>
</body>
</html>