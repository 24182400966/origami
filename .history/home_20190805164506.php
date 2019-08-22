<?php
$carousels = [];
for ($i = 1; $i < 5; $i++) {
  $url = get_option("origami_carousel_" . $i, "");
  if ($url != "") {
    $carousels[] = $url;
  }
}
?>

<?php get_header(); ?>
<main id="main-content">
    <div class="carousel">
        <?php foreach ($carousels as $index => $url): ?>
        <input class="carousel-locator" id="slide-1" type="radio" name="carousel-radio" hidden="">
<?php endforeach; ?>
        <div class="carousel-container">
            <figure class="carousel-item">
                <label class="item-prev btn btn-action btn-lg" for="slide-4"><i class="icon icon-arrow-left"></i></label>
                <label class="item-next btn btn-action btn-lg" for="slide-2"><i class="icon icon-arrow-right"></i></label>
                <img class="img-responsive rounded" style="background-image:url('https://blog.ixk.me/bing-api.php?size=1024x768&day=1')">
            </figure>
            <figure class="carousel-item">
                <label class="item-prev btn btn-action btn-lg" for="slide-1"><i class="icon icon-arrow-left"></i></label>
                <label class="item-next btn btn-action btn-lg" for="slide-3"><i class="icon icon-arrow-right"></i></label>
                <div class="img-responsive rounded" style="background-image:url('https://blog.ixk.me/bing-api.php?size=1024x768&day=2')">
            </figure>
            <figure class="carousel-item">
                <label class="item-prev btn btn-action btn-lg" for="slide-2"><i class="icon icon-arrow-left"></i></label>
                <label class="item-next btn btn-action btn-lg" for="slide-4"><i class="icon icon-arrow-right"></i></label>
                <div class="img-responsive rounded" style="background-image:url('https://blog.ixk.me/bing-api.php?size=1024x768&day=3')">
            </figure>
            <figure class="carousel-item">
                <label class="item-prev btn btn-action btn-lg" for="slide-3"><i class="icon icon-arrow-left"></i></label>
                <label class="item-next btn btn-action btn-lg" for="slide-1"><i class="icon icon-arrow-right"></i></label>
                <div class="img-responsive rounded" style="background-image:url('https://blog.ixk.me/bing-api.php?size=1024x768&day=4')">
            </figure>
        </div>
        <div class="carousel-nav">
            <label class="nav-item text-hide c-hand" for="slide-1">1</label>
            <label class="nav-item text-hide c-hand" for="slide-2">2</label>
            <label class="nav-item text-hide c-hand" for="slide-3">3</label>
            <label class="nav-item text-hide c-hand" for="slide-4">4</label>
        </div>
    </div>
    <?php get_template_part('template-part/content'); ?>
</div>
<?php get_footer(); ?>