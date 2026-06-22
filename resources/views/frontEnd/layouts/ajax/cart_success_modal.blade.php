<div class="modal-view success-modal-content">
    <button class="close-modal close-success-modal">x</button>
    <div class="success-content text-center">
        <div class="success-icon">
            <i class="fa-solid fa-check-circle" style="color: #28a745; font-size: 50px; margin-bottom: 20px;"></i>
        </div>
        <h4>Success!</h4>
        <p>Product added to cart successfully.</p>
        <div class="success-buttons mt-4">
            <button class="btn btn-secondary close-success-modal">Continue Shopping</button>
            <a href="{{ route('customer.checkout') }}" class="btn btn-primary">Checkout</a>
        </div>
    </div>
</div>

<style>
    .success-modal-content {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        width: 400px;
        max-width: 90%;
        margin: 0 auto;
        position: fixed; /* or absolute depending on your modal system */
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
    }
</style>

<script>
    $('.close-success-modal').on('click', function() {
        $("#success-modal").hide();
        $("#page-overlay").hide();
    });
</script>
