<div x-data="productManager()" class="product-manager">
    <h3>Manage Related Products</h3>
    <sl-select multiple name="related_products" x-model="relatedProducts">
        <?php foreach ($allProducts as $relatedProduct): ?>
            <?php if ($relatedProduct->id != $product->id): ?>
                <sl-menu-item value="<?= $relatedProduct->id ?>"><?= htmlspecialchars($relatedProduct->name) ?></sl-menu-item>
            <?php endif; ?>
        <?php endforeach; ?>
    </sl-select>
    <sl-button @click="updateRelatedProducts">Update Related Products</sl-button>

    <h3>Manage FAQs</h3>
    <div class="faq-manager">
        <sl-form @submit.prevent="addFAQ">
            <sl-input name="question" label="Question" x-model="newQuestion" required></sl-input>
            <sl-textarea name="answer" label="Answer" x-model="newAnswer" required></sl-textarea>
            <sl-button type="submit">Add FAQ</sl-button>
        </sl-form>

        <div class="faq-list">
            <template x-for="(faq, index) in faqs" :key="faq.id">
                <sl-card class="faq-item">
                    <sl-input name="question" label="Question" x-model="faq.question"></sl-input>
                    <sl-textarea name="answer" label="Answer" x-model="faq.answer"></sl-textarea>
                    <sl-button-group>
                        <sl-button @click="updateFAQ(index)">Update</sl-button>
                        <sl-button @click="deleteFAQ(index)" variant="danger">Delete</sl-button>
                    </sl-button-group>
                </sl-card>
            </template>
        </div>
    </div>
</div>

<script>
function productManager() {
    return {
        productId: <?= json_encode($product->id) ?>,
        relatedProducts: <?= json_encode($product->getRelatedProductIds()) ?>,
        faqs: <?= json_encode($product->getFAQs()) ?>,
        newQuestion: '',
        newAnswer: '',

        updateRelatedProducts() {
            fetch('/seller/update-related-products', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: this.productId,
                    related_products: this.relatedProducts
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Related products updated successfully');
                } else {
                    alert('Failed to update related products');
                }
            });
        },

        addFAQ() {
            fetch('/seller/manage-faq', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    productId: this.productId,
                    question: this.newQuestion,
                    answer: this.newAnswer
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.faqs.push({
                        id: data.faqId,
                        question: this.newQuestion,
                        answer: this.newAnswer
                    });
                    this.newQuestion = '';
                    this.newAnswer = '';
                } else {
                    alert('Failed to add FAQ');
                }
            });
        },

        updateFAQ(index) {
            const faq = this.faqs[index];
            fetch('/seller/manage-faq', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    productId: this.productId,
                    faqId: faq.id,
                    question: faq.question,
                    answer: faq.answer
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('FAQ updated successfully');
                } else {
                    alert('Failed to update FAQ');
                }
            });
        },

        deleteFAQ(index) {
            const faq = this.faqs[index];
            fetch('/seller/manage-faq', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete',
                    productId: this.productId,
                    faqId: faq.id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.faqs.splice(index, 1);
                } else {
                    alert('Failed to delete FAQ');
                }
            });
        }
    }
}

function faqManager() {
    return {
        faqs: <?= json_encode($product->getFAQs()) ?>,
        newQuestion: '',
        newAnswer: '',
        addFAQ() {
            fetch('/seller/manage-faq', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add', productId: <?= $product->id ?>, question: this.newQuestion, answer: this.newAnswer })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.faqs.push({ id: data.faqId, question: this.newQuestion, answer: this.newAnswer });
                    this.newQuestion = '';
                    this.newAnswer = '';
                }
            });
        },
        updateFAQ(index) {
            const faq = this.faqs[index];
            fetch('/seller/manage-faq', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update', productId: <?= $product->id ?>, faqId: faq.id, question: faq.question, answer: faq.answer })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // FAQ updated successfully
                }
            });
        },
        deleteFAQ(index) {
            const faq = this.faqs[index];
            fetch('/seller/manage-faq', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', productId: <?= $product->id ?>, faqId: faq.id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.faqs.splice(index, 1);
                }
            });
        }
    }
}
</script>