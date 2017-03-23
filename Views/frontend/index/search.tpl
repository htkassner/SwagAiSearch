{extends file="parent:frontend/index/search.tpl"}

{block name='frontend_index_search_container'}
    <div class="search--container" data-imageSearch="true">

        <div class="search--switch">
            <a href="#" title="Bilder-Suche" class="btn btn--search-image">
                <i class="icon--pictures"></i>
            </a>
        </div>

        <div class="search--main">
            {$smarty.block.parent}
        </div>

        <div class="search--images">

            <a href="#" title="Web-Cam Aufnahme" class="btn btn--webcam-image">
                <i class="icon--camera"></i>
            </a>

            <div class="search--image-url">
                <input type="text" name="imageurl" id="image-url" class="search--image-input" placeholder="Bild URL" />
            </div>

            <div class="search--webcam-video">
                <a href="#" class="btn is--primary search--webcam-snapshot">
                    <i class="icon--camera"></i> {s name="snapshot/button/label"}Snapshot{/s}
                </a>
            </div>
        </div>

    </div>
{/block}