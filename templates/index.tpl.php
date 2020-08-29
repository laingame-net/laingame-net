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
.login {
    float: right;
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
</style>
</head>
<body>
<div class="container">
<div class="login"><?=(@$_SESSION['user']) ? 'Logged in as <a href="/site/logout">'.@$_SESSION['user']->name.'</a>.</br>'
                       : '<a href="/site/login">Login</a> or <a href="/site/register">Register</a></br>'?></div>
<?=$CONTENT?>
</div>
</body>
