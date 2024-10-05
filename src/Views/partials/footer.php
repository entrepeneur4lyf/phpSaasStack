<footer x-data="{ currentYear: new Date().getFullYear() }">
    <div class="container mx-auto px-6 py-4">
        <div class="flex flex-wrap justify-between items-center">
            <div class="w-full md:w-1/3 text-center md:text-left">
                <h3 class="text-lg font-semibold">SaaS AI Marketplace</h3>
                <p class="mt-2 text-sm">Empowering AI innovations</p>
            </div>
            <div class="w-full md:w-1/3 text-center mt-4 md:mt-0">
                <nav>
                    <ul class="flex justify-center space-x-4">
                        <li><a href="/about" class="text-gray-600 hover:text-gray-800">About</a></li>
                        <li><a href="/terms" class="text-gray-600 hover:text-gray-800">Terms</a></li>
                        <li><a href="/privacy" class="text-gray-600 hover:text-gray-800">Privacy</a></li>
                        <li><a href="/contact" class="text-gray-600 hover:text-gray-800">Contact</a></li>
                    </ul>
                </nav>
            </div>
            <div class="w-full md:w-1/3 text-center md:text-right mt-4 md:mt-0">
                <div class="flex justify-center md:justify-end space-x-4">
                    <a href="#" aria-label="Twitter">
                        <sl-icon name="twitter" label="Twitter"></sl-icon>
                    </a>
                    <a href="#" aria-label="Facebook">
                        <sl-icon name="facebook" label="Facebook"></sl-icon>
                    </a>
                    <a href="#" aria-label="Instagram">
                        <sl-icon name="instagram" label="Instagram"></sl-icon>
                    </a>
                    <a href="#" aria-label="GitHub">
                        <sl-icon name="github" label="GitHub"></sl-icon>
                    </a>
                </div>
            </div>
        </div>
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-600">
                &copy; <span x-text="currentYear"></span> SaaS AI Marketplace. All rights reserved.
            </p>
        </div>
    </div>
</footer>