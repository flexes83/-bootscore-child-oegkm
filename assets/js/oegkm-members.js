
document.addEventListener('click', function(e){

    const videoButton = e.target.closest('.oegkm-video-placeholder');

    if(videoButton){
        const token = videoButton.dataset.oegkmVideoToken || '';
        let video = {};

        try {
            video = token ? JSON.parse(window.atob(token)) : {};
        } catch (error) {
            video = {};
        }

        if(video.id && video.p){
            const iframe = document.createElement('iframe');
            iframe.src = video.p === 'vm'
                ? 'https://player.' + 'vimeo.com/video/' + encodeURIComponent(video.id) + '?autoplay=1'
                : 'https://www.' + 'youtube-nocookie.com/embed/' + encodeURIComponent(video.id) + '?autoplay=1&rel=0';
            iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
            iframe.allowFullscreen = true;

            const wrapper = document.createElement('div');
            wrapper.className = 'oegkm-event-media-video__embed';
            wrapper.appendChild(iframe);

            videoButton.replaceWith(wrapper);
        }
    }

    const imageLink = e.target.closest('[data-lightbox-src]');

    if(imageLink){
        e.preventDefault();

        const overlay = document.createElement('div');
        overlay.className = 'oegkm-lightbox';

        const img = document.createElement('img');
        img.src = imageLink.dataset.lightboxSrc;

        overlay.appendChild(img);

        overlay.addEventListener('click', function(){
            overlay.remove();
        });

        document.body.appendChild(overlay);
    }

});
