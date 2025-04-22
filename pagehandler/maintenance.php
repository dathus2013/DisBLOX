<?php
    use watergames\pagebuilder;
    $pagebuilder = new pagebuilder;
    $pagebuilder->set_page_name("Maintenance");
    $pagebuilder->buildheader();

?>
<div id="main">
    <img src="/assets/images/logo.svg" id="logo">
    <p>Is currently under maintenance.</p>
    <p>Come back later!</p>
    <form method="post">
        <?php if(isset($mainerror)){
            echo "<p style=\"color: red;\">$mainerror</p>";
            $class = "error";
        }
        ?>
        <input type="password" name="maintkey" placeholder="" class="input <?=$class ?? "" ?> ">
        <input type="submit" value="enter" class="button">
    </form>
    
</div>


<?php $pagebuilder->get_snippet("footer"); ?>