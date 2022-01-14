








    <?php
 
 // connect with database
 $conn = new PDO("mysql:host=localhost;dbname=bestenbeautycom1", "bestenbeautycom", "d1km6s7n");

 // create table to store latest instagram feeds
 $sql = "CREATE TABLE IF NOT EXISTS instagram (
     id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
     url TEXT DEFAULT NULL,
     image_path TEXT DEFAULT NULL,
     video_path TEXT DEFAULT NULL,
     caption TEXT DEFAULT NULL,
     likes TEXT DEFAULT NULL,
     comments TEXT DEFAULT NULL
 )";
 $result = $conn->prepare($sql);
 $result->execute();











    // check if form is submitted
    if (isset($_POST["submit"]))
    {
    // get JSON from textarea
    $json = $_POST["json"];

    // decode JSON into arrays and objects
    $content = json_decode($json);

    // get all the latest posts
    $edges = $content->graphql->user->edge_owner_to_timeline_media->edges;

    mkdir("instagram-media");

    // delete previous posts from our database
    $sql = "DELETE FROM instagram";
    $result = $conn->prepare($sql);
    $result->execute();

        // loop through all posts
        foreach ($edges as $edge)
        {
            // get single post
            $node = $edge->node;

            // get URL shortcode of post
            $url = $node->shortcode;

            // get caption, if any
            $caption = $node->edge_media_to_caption->edges[0]->node->text;

            // get number of likes
            $likes = $node->edge_liked_by->count;

            // get total number of comments
            $comments = $node->edge_media_to_comment->count;

            // save image in our server if uploaded
            $image_path = "";
            if (!is_null($node->display_url))
            {
                $image_path = "instagram-media/" . $url . ".png";
                file_put_contents($image_path, file_get_contents($node->display_url));
            }

            // save video in our server if uploaded
            $video_path = "";
            if (!is_null($node->video_url))
            {
                $video_path = "instagram-media/" . $url . ".mp4";
                file_put_contents($video_path, file_get_contents($node->video_url));
            }

            // insert in database
            $sql = "INSERT INTO instagram(url, image_path, video_path, caption, likes, comments) VALUES (:url, :image_path, :video_path, :caption, :likes, :comments)";
            $result = $conn->prepare($sql);
            $result->execute([
                ":url" => $url,
                ":image_path" => $image_path,
                ":video_path" => $video_path,
                ":caption" => $caption,
                ":likes" => $likes,
                ":comments" => $comments
            ]);
        }

    echo "<p>Done</p>";
    }



    // get all posts from database
    $sql = "SELECT * FROM instagram ORDER BY id ASC";
    $result = $conn->query($sql);
    $instagram_feed = $result->fetchAll();

    ?>







<main >
    <div class="container">
        <div class="gallery">
 
            <!-- loop through all rows from database -->
            <?php foreach ($instagram_feed as $feed): ?>
                <div class="gallery-item" tabindex="0">
                    <!-- wrap with anchor tag, when clicked will go to instagram detail post page -->
                    <a href="https://www.instagram.com/p/<?php echo $feed['url']; ?>" target="_blank" style="color: white;">
 
                        <!-- thumbnail of post -->
                        <img src="<?php echo $feed['image_path']; ?>" class="gallery-image" />
 
                        <div class="gallery-item-info">
                            <ul>
                                <!-- show no. of likes -->
                                <li class="gallery-item-likes">
                                    <i class="fa fa-heart"></i>
                                    <?php echo $feed["likes"]; ?>
                                </li>
                                 
                                <!-- show no. of comments -->
                                <li class="gallery-item-comments">
                                    <i class="fa fa-comment"></i>
                                    <?php echo $feed["comments"]; ?>
                                </li>
                            </ul>
 
                            <!-- show caption -->
                            <p><?php echo $feed["caption"]; ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
 
        </div>
    </div>
</main>

<!--

<form method="POST" action="index.php">
    <p>
        <a href="https://www.instagram.com/bestenbeauty/?__a=1">Goto this link and paste the JSON string in textbox below</a>
    </p>
 
    <p>
        <label>Paste JSON</label>
        <textarea name="json" rows="10" cols="30" required></textarea>
    </p>
 
    <input type="submit" name="submit" class="Save" />
</form> 

-->

 
<!-- style CSS -->
<link rel="stylesheet" type="text/css" href="instagram.css?v=<?php echo time(); ?>" />
 
<!-- font awesome -->
<script src="https://use.fontawesome.com/b8680c3f3d.js"></script>



