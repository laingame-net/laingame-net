<!DOCTYPE html>
<html>
<head>
<title><?=$TITLE?></title>
<style>
html, body{
    width:100%;
    height:100%;
    margin:0;
    padding:0;
}
table,tr,td,th {
    border: 1px solid black;
}
.h50{
    height: 50px;
}
.h39{
    height: 39px;
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
.block {
   width: 640px;
}
.container {
   width: 640px;
}
.login {
    float: right;
}
.btn {
    background: #32a852;
}
/* diff */
del {
    color: red;
    background: #fdd;
    text-decoration: none;
}
ins {
    color: green;
    background: #dfd;
    text-decoration: none;
}
textarea { 
    resize: both;
    width: 100%;
    width: -moz-available;
    width: -webkit-fill-available;
    width: fill-available;
    font-size: 15px;
    background: #fff5de;
}
input{
    width: 100%;
    width: -moz-available;
    width: -webkit-fill-available;
    width: fill-available;
}
form{
    display: grid;
}
.sticky{
    position: -webkit-sticky; /* Safari */
    position: sticky;
    top: 0px;
    background: white;
}
.float-global{
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-gap: 10px;
}
.global{
}
.changes {
    width: 400px;
}
</style>
<script src="/js/autosize.min.js"></script>
</head>
<body>
<div class="container">
<div class="login"><?=(@$_SESSION['user']) ? 'Logged in as <a href="/site/logout">'.htmlspecialchars($_SESSION['user']->name).'</a>.</br>'
                       : '<a href="/site/login">Login</a> or <a href="/site/register">Register</a></br>'?></div>
</div>
<div class="global">
<?=$CONTENT?>
</div>
</body>
