<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product->name) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/shoelace.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/themes/light.css">
</head>
<body>
    <div x-data="productDetail()">
        <h1><?= htmlspecialchars($product->name) ?></h1>
        
        <h3>Related Products</h3>
        <div class="related-products-carousel">
            <sl-button @click="prev" size="small">&lt;</sl-button>
            <div class="carousel-container">
                <div class="carousel-track" x-ref="track" :style="{ transform: `translateX(-${currentIndex * 100}%)` }">
                    <template x-for="product in relatedProducts" :key="product.id">
                        <div class="carousel-item">
                            <sl-card>
                                <img slot="image" :src="product.image_url" :alt="product.name">
                                <strong x-text="product.name"></strong>
                                <small x-text="`$${product.price.toFixed(2)}`"></small>
                                <sl-button slot="footer" @click="viewProduct(product.id)">View</sl-button>
                            </sl-card>
                        </div>
                    </template>
                </div>
            </div>
            <sl-button @click="next" size="small">&gt;</sl-button>
        </div>

        <h3>FAQs</h3>
        <sl-details-group>
            <template x-for="faq in faqs" :key="faq.id">
                <sl-details>
                    <strong slot="summary" x-text="faq.question"></strong>
                    <p x-text="faq.answer"></p>
                </sl-details>
            </template>
        </sl-details-group>
    </div>

    <script>
    function productDetail() {
        return {
            relatedProducts: <?= json_encode($relatedProducts) ?>,
            faqs: <?= json_encode($faqs) ?>,
            currentIndex: 0,
            init() {
                this.items = this.$refs.track.children;
            },
            next() {
                this.currentIndex = this.currentIndex === this.items.length - 1 ? 0 : this.currentIndex + 1;
            },
            prev() {
                this.currentIndex = this.currentIndex === 0 ? this.items.length - 1 : this.currentIndex - 1;
            },
            viewProduct(id) {
                window.location.href = `/product/${id}`;
            }
        }
    }
    </script>

    <style>
    .related-products-carousel {
        display: flex;
        align-items: center;
    }
    .carousel-container {
        overflow: hidden;
        width: 100%;
    }
    .carousel-track {
        display: flex;
        transition: transform 0.3s ease-in-out;
    }
    .carousel-item {
        flex: 0 0 100%;
        padding: 0 10px;
        box-sizing: border-box;
    }
    @media (min-width: 768px) {
        .carousel-item {
            flex: 0 0 33.333%;
        }
    }
    </style>
</body>
</html>