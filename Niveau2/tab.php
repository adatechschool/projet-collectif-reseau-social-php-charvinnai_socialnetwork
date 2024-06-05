<?php
if (isset($_SESSION['connected_id']) == false) {
            ?>

                <a href="login.php">Actualités</a>
                <a href="login.php">Mur</a>
                <a href="login.php">Flux</a>
                <a href="login.php">Mots-clés</a>

<?php   
}else{?>
                <a href="news.php">Actualités</a>
                <a href="wall.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Mur</a>
                <a href="feed.php?user_id=<?php echo $_SESSION['connected_id'] ?>">Flux</a>
                <a href="tags.php?tag_id=1">Mots-clés</a>
<?php
};
?>