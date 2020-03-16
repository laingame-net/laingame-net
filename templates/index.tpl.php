<!DOCTYPE html>
<html>
<head>
<title><?=$TITLE?></title>
<style>
html, body{
    width:80%;
    height:100%;
    margin:0;
    padding:0;
}
table,tr,td,th {
    border: 1px solid black;
}
input {
    width:100%;
}
.table {
    display:table;
    width:100%;
    height: 100%;
    table-layout: fixed;
}
.cell {
    display:table-cell;
    /* width:25%; */
    color:black;
    border:1px solid black;
}
.row {
    display:table-row;
}
.level {
    text-align: center;
}
.container {
   width: 640px;
   /* height: 200; */
}
</style>
</head>
<body>
<div class="container">
<?=$CONTENT?>
</div>
</body>
