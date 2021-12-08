<?php

# SELECT `TableKey`, `ColumnKey`, `ColumnID` FROM `Dimension.dTable`
# $sql = "SELECT customerid, companyname, contactname, address, city, postalcode, country
# FROM customers WHERE customerid = ?";

$mysqli = new mysqli("", "", "", "");
if($mysqli->connect_error) {
    exit('Could not connect');
}

$sql = "SELECT `TableKey`, `ColumnKey`, `ColumnID` FROM `Dimension.dTable` WHERE `TableKey` = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $_GET['q']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($tkey, $ckey, $columnid);

echo "<hr class='mt-0 shadow-sm' style='color: #181618; height: 1px; opacity: 0.5'>";

echo "<div class='col-12'>";
    echo "<div class='col-12 p-3 bg-textbox shadow-sm'>";
        echo "<div class='row mb-3'>";

            echo "<div class='col-md-2 col-12 py-3'>";
                echo "<span style='color: #fff'>Dimension Name</span>";
            echo "</div>";

            # Insert Dimension Name
            echo "<div class='col-md-4 col-12'>";
                echo "<input class='w-100 py-1 my-2 h-100' type='text' value='dh' id='dimensionNameColumn' style='border: 3px solid #4350CA; color: #23304D; padding-left: 10px'/>";
            echo "</div>";

            # Choose SID
            echo "<div class='col-md-2 col-12'>";
                echo "<select class='w-100 py-1 my-2 h-100' id='SelectedMainSID' style='border: 3px solid #4350CA; color: #23304D; padding-left: 10px'>";
                    echo "<option disabled selected>Select SID</option>";

                    # Generate possible SID columns
                    if ($stmt->num_rows() > 0) {
                        while($stmt->fetch())
                         {
                            echo "<option>$ckey</option>";

                         }
                    }

                echo "</select>";
            echo "</div>";

            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($tkey, $ckey, $columnid);

            # Choose Key
            echo "<div class='col-md-2 col-12'>";
                echo "<select class='w-100 py-1 my-2 h-100' id='SelectedMainKEY' style='border: 3px solid #4350CA; color: #23304D; padding-left: 10px'>";
                    echo "<option disabled selected>Select Key</option>";

                    # Generate possible SID columns
                    if ($stmt->num_rows() > 0) {
                        while($stmt->fetch())
                        {
                            echo "<option>$ckey</option>";
                        }
                    }

                echo "</select>";
            echo "</div>";

            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($tkey, $ckey, $columnid);

            # Generate Code Button
            echo "<div class='col-md-2 col-12'>";
                echo "<button class='btn btn-primary w-100 py-3 h-100' onclick='generateCode()' style='border-radius: 0; color: #ccc; font-weight: bold; margin-top: 8px'><i class='fa fa-code' aria-hidden='true'></i> Generate Code</button>";
            echo "</div>";

        echo "</div>";
    echo "</div>";
echo "</div>";

echo "<hr class='mt-0 shadow-sm' style='color: #181618; height: 1px; opacity: 0.5'>";

# REQUIRED INPUT FIELDS
echo "<div class='col-12'>";
    echo "<div class='col-12 mb-3 p-3 shadow-sm' style='background: #181618'>";
        echo "<div class='row'>";

            echo "<div class='col-md-2 bg-textbox' style='background: #181618'></div>";
            echo "<div class='col-md-4 col-5 bg-textbox' style='background: #181618'>";
                echo "<p class='mb-0' style='font-weight: bold'><i class='fas fa-align-center'></i> Column name</p>";
            echo "</div>";

            echo "<div class='col-md-4 col-5 bg-textbox' style='background: #181618'>";
                echo "<p class='mb-0' style='font-weight: bold'><i class='fas fa-key'></i> SpecialKey</p>";
            echo "</div>";

            echo "<div class='col-md-2 col-2 bg-textbox text-center' style='background: #181618'>";
                echo "<p class='mb-0' style='font-weight: bold'><i class='fas fa-hashtag'></i> Hash</p>";
            echo "</div>";

        echo "</div>";
    echo "</div>";
echo "</div>";

if ($stmt->num_rows() > 0) {
    while($stmt->fetch())
    {
        echo "<div class='col-12 p-3 mb-3 bg-textbox shadow-sm'>";
            echo "<div class='row mb-2r'>";

                # Get columns for each dimension
                echo "<div class='col-md-2 col-12 py-3'>";
                    echo "<span style='color: #fff'>$columnid. Column</span>";
                echo "</div>";

                echo "<div class='col-md-4 col-5'>";
                    echo "<input class='w-100 py-1 my-2 h-100' name='columnName' style='color: #23304D; padding-left: 10px' type='text' id='input_$ckey' placeholder='$ckey'' value='$ckey'/>";
                echo "</div>";


                # Generate special key columns
                # Check for specific strings within the generated columnNames to auto select special key column
                echo "<div class='col-md-4 col-5'>";
                    echo "<select class='form-select py-1 my-2 h-100' id='SpecialKey$ckey' style='border-radius: 0'>";

                        # if column name contains 'SID' select SpecialKeySID
                        if (strpos(substr($ckey,-3),"SID") !== false) {
                            echo "<option disabled></option>";
                            echo "<option value='1' selected>SpecialKeySID</option>";
                            echo "<option value='2'>SpecialKeyKey</option>";
                            echo "<option value='3'>SpecialKeyNameEN</option>";
                            echo "<option value='4'>SpecialKeyNameDE</option>";
                            echo "<option value='5'>0</option>";
                        }
                        # else if column name ends with 'SID' select SpecialKeyKey
                        else if (strpos(substr($ckey,-3),"Key") !== false) {
                            echo "<option disabled></option>";
                            echo "<option value='1'>SpecialKeySID</option>";
                            echo "<option value='2' selected>SpecialKeyKey</option>";
                            echo "<option value='3'>SpecialKeyNameEN</option>";
                            echo "<option value='4'>SpecialKeyNameDE</option>";
                            echo "<option value='5'>0</option>";
                        }
                        # else if column name ends with 'NameEN' select SpecialKeyNameEN
                        else if (strpos(substr($ckey,-6),"NameEN") !== false) {
                            echo "<option disabled></option>";
                            echo "<option value='1'>SpecialKeySID</option>";
                            echo "<option value='2'>SpecialKeyKey</option>";
                            echo "<option value='3' selected>SpecialKeyNameEN</option>";
                            echo "<option value='4'>SpecialKeyNameDE</option>";
                            echo "<option value='5'>0</option>";
                        }
                        # else if column name ends with 'NameDE' select SpecialKeyNameDE
                        else if (strpos(substr($ckey,-6),"NameDE") !== false) {
                            echo "<option disabled></option>";
                            echo "<option value='1'>SpecialKeySID</option>";
                            echo "<option value='2'>SpecialKeyKey</option>";
                            echo "<option value='3'>SpecialKeyNameEN</option>";
                            echo "<option value='4' selected>SpecialKeyNameDE</option>";
                            echo "<option value='5'>0</option>";
                        }
                        # else if column name ends with 'Name' select SpecialKeyNameEN
                        else if (strpos(substr($ckey,-4),"Name") !== false) {
                            echo "<option disabled></option>";
                            echo "<option value='1'>SpecialKeySID</option>";
                            echo "<option value='2'>SpecialKeyKey</option>";
                            echo "<option value='3' selected>SpecialKeyNameEN</option>";
                            echo "<option value='4'>SpecialKeyNameDE</option>";
                            echo "<option value='5'>0</option>";
                        }
                        # else if requirements are not matching
                        else {
                            echo "<option disabled></option>";
                            echo "<option value='1'>SpecialKeySID</option>";
                            echo "<option value='2'>SpecialKeyKey</option>";
                            echo "<option value='3'>SpecialKeyNameEN</option>";
                            echo "<option value='4'>SpecialKeyNameDE</option>";
                            echo "<option value='5' selected>0</option>";
                        }
                    echo "</select>";
                echo "</div>";

                # SID column: SHA2_256 checkbox
                # Auto check checkbox if columnName ends with string "NameEN", "NameDE" or "Name"
                echo "<div class='col-md-2 col-2 py-3' style='text-align: center'>";

                    # if column name ends with "NameEN" auto check checkbox
                    if (strpos(substr($ckey,-6),"NameEN") !== false) {
                        echo "<input type='checkbox' id='Checkbox$ckey' checked style='margin-top: 20px; transform: scale(4.5)'>";
                    }
                    # else if column name ends with "NameDE" auto check checkbox
                    else if (strpos(substr($ckey,-6),"NameDE") !== false) {
                        echo "<input type='checkbox' id='Checkbox$ckey' checked style='margin-top: 20px; transform: scale(4.5)'>";
                    }
                    # else if column name ends with 'Name' auto check checkbox
                    else if (strpos(substr($ckey,-4),"Name") !== false) {
                        echo "<input type='checkbox' id='Checkbox$ckey' checked style='margin-top: 20px; transform: scale(4.5)'>";
                    }
                    else {
                        echo "<input type='checkbox' id='Checkbox$ckey' style='margin-top: 20px; transform: scale(4.5)'>";
                    }
                echo "</div>";

            echo "</div>";
        echo "</div>";
    }
}

echo "<hr class='mt-0 shadow-sm' style='color: #181618; height: 1px; opacity: 0.5'>";

# Generate CTE input field
echo "<div class='col-12 p-3 mb-3 bg-textbox shadow-sm'>";
    echo "<div class='row'>";
        echo "<div class='col-12 col-md-2'>";
            echo "<span style='color: #fff'>CTE</span>";
        echo "</div>";

        echo "<div class='col-12 col-md-8'>";
            echo "<textarea class='w-100' cols='50' rows='5' placeholder='... insert CTE code' id='cte'></textarea>";
        echo "</div>";
    echo "</div>";
echo "</div>";

# Generate Code Button and Copy Button
echo "<div class='col-12 p-3 mb-3 bg-textbox shadow-sm'>";
    echo "<div class='row'>";
        echo "<div class='col-12 col-md-2'></div>";
        echo "<div class='col-12 col-md-4'>";
            echo "<button class='btn btn-primary w-100 py-4' onclick='generateCode()' style='border-radius: 0; color: #ccc; font-weight: bold'><i class='fa fa-code' aria-hidden='true'></i> Generate Code</button>";
        echo "</div>";
        echo "<div class='col-12 col-md-4'>";
            echo "<button class='btn btn-secondary w-100 py-4' onclick='copyFunction()' id='copybutton' style='border-radius: 0; color: #ccc; font-weight: bold'><i class='fa fa-clone' aria-hidden='true'></i> Copy Code</button>";
        echo "</div>";
        echo "<div class='col-12 col-md-2'></div>";
    echo "</div>";
echo "</div>";

include('editor.php');


$stmt->close();


