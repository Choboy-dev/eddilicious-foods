<?php
/**
 * Admin user manual — help articles for shop owners.
 *
 * @return array{
 *   categories: list<array{id: string, label: string, icon: string}>,
 *   articles: list<array<string, mixed>>
 * }
 */
function get_admin_help_manual(): array
{
    return [
        'categories' => [
            ['id' => 'start', 'label' => 'Getting started', 'icon' => 'start'],
            ['id' => 'overview', 'label' => 'Overview', 'icon' => 'overview'],
            ['id' => 'orders', 'label' => 'Orders', 'icon' => 'orders'],
            ['id' => 'meals', 'label' => 'Meals', 'icon' => 'meals'],
            ['id' => 'coupons', 'label' => 'Coupons', 'icon' => 'coupons'],
            ['id' => 'analytics', 'label' => 'Analytics', 'icon' => 'analytics'],
            ['id' => 'shop', 'label' => 'Your shop', 'icon' => 'shop'],
            ['id' => 'limits', 'label' => 'What you cannot change here', 'icon' => 'limits'],
            ['id' => 'help', 'label' => 'Tips and fixes', 'icon' => 'help'],
        ],
        'articles' => [
            [
                'id' => 'what-is-admin',
                'category' => 'start',
                'title' => 'What is the admin area?',
                'keywords' => 'admin dashboard back office control panel owner',
                'summary' => 'The admin area is where you run your online shop.',
                'blocks' => [
                    ['type' => 'text', 'text' => 'The admin area is a private part of your website. Only you and other owners can open it. Customers cannot see it.'],
                    ['type' => 'list', 'title' => 'From admin you can', 'items' => [
                        'See how much money you made',
                        'Read order lists',
                        'Add and edit meals on the menu',
                        'Change photos, prices, and descriptions',
                        'Make discount codes',
                        'See how many people visit your site',
                    ]],
                    ['type' => 'list', 'title' => 'Open admin at', 'items' => [
                        'Your website address plus /admin/signin.php',
                        'Example on your live site: eddiliciousfoods.co.uk/admin/signin.php',
                    ]],
                ],
            ],
            [
                'id' => 'sign-in',
                'category' => 'start',
                'title' => 'How to sign in',
                'keywords' => 'login log in sign in email code otp password',
                'summary' => 'You sign in with your email and a 6-digit code.',
                'blocks' => [
                    ['type' => 'steps', 'title' => 'Steps', 'items' => [
                        'Open admin/signin.php in your browser.',
                        'Type the owner email your developer gave you.',
                        'Tap Continue.',
                        'Check your email for a 6-digit code.',
                        'Type the code. It fills in on its own when you finish.',
                        'You land on the Overview page.',
                    ]],
                    ['type' => 'list', 'title' => 'Good to know', 'items' => [
                        'The code lasts about 10 minutes.',
                        'Tap Resend code if you need a new one. Wait 30 seconds between tries.',
                        'There is no password. The email code is your key.',
                        'Only emails on the owner list can sign in. Ask your developer to add a new owner email.',
                    ]],
                ],
            ],
            [
                'id' => 'sign-out',
                'category' => 'start',
                'title' => 'How to sign out',
                'keywords' => 'logout log out sign out exit leave',
                'summary' => 'Use Sign out at the bottom of the left menu.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'Look at the left menu.',
                        'Scroll to the bottom.',
                        'Tap Sign out.',
                        'You go back to the sign-in page.',
                    ]],
                ],
            ],
            [
                'id' => 'admin-menu',
                'category' => 'start',
                'title' => 'The left menu explained',
                'keywords' => 'sidebar navigation menu links pages',
                'summary' => 'Every admin page is in the left menu.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Overview — sales totals and charts',
                        'Orders — list of customer orders',
                        'Meals — add and edit food on the menu',
                        'Coupons — discount codes for customers',
                        'Analytics — website visitor stats',
                        'User manual — this help page',
                    ]],
                    ['type' => 'list', 'title' => 'Bottom links', 'items' => [
                        'Customer view — opens your account page as a shopper sees it',
                        'View shop — opens the menu in a new tab',
                        'Sign out — ends your admin session',
                    ]],
                ],
            ],
            [
                'id' => 'overview-basics',
                'category' => 'overview',
                'title' => 'Overview page basics',
                'keywords' => 'overview dashboard home sales revenue money chart',
                'summary' => 'Overview shows how your shop is doing.',
                'blocks' => [
                    ['type' => 'text', 'text' => 'Open Overview from the left menu. This is the first page after you sign in.'],
                    ['type' => 'list', 'title' => 'You will see', 'items' => [
                        'Revenue — money from paid orders in the time you picked',
                        'Paid orders — orders customers finished paying for',
                        'Pending — orders not finished yet',
                        'Failed — payments that did not work',
                        'Cancelled — orders that were cancelled',
                        'A revenue chart',
                        'A short list of recent orders',
                    ]],
                    ['type' => 'text', 'text' => 'Revenue only counts orders paid through Stripe. If Stripe is not set up yet, numbers may stay at zero.'],
                ],
            ],
            [
                'id' => 'overview-dates',
                'category' => 'overview',
                'title' => 'Pick a date range on Overview',
                'keywords' => 'date filter today week month custom period range',
                'summary' => 'Change the time period to see different sales numbers.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'At the top of Overview, find the date buttons.',
                        'Tap Today, 7 days, 30 days, or All time.',
                        'The numbers and chart update.',
                    ]],
                    ['type' => 'steps', 'title' => 'Custom dates', 'items' => [
                        'Tap Custom.',
                        'Pick a From date and a To date.',
                        'Tap Apply dates.',
                        'You can pick up to about one year at a time.',
                    ]],
                ],
            ],
            [
                'id' => 'overview-chart',
                'category' => 'overview',
                'title' => 'Read the revenue chart',
                'keywords' => 'chart graph bar revenue daily weekly monthly',
                'summary' => 'The chart shows paid order money over time.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Short periods (today or 7 days) show daily bars.',
                        'Longer periods may group by week or month.',
                        'Only paid orders count.',
                        'Hover or read the labels under each bar for the amount.',
                    ]],
                ],
            ],
            [
                'id' => 'orders-basics',
                'category' => 'orders',
                'title' => 'Orders page basics',
                'keywords' => 'orders list customers purchases',
                'summary' => 'Orders shows who bought food and what they paid.',
                'blocks' => [
                    ['type' => 'text', 'text' => 'Open Orders from the left menu. You see a table of orders.'],
                    ['type' => 'list', 'title' => 'Each row shows', 'items' => [
                        'Order reference — a short code for the order',
                        'Customer name, email, and phone',
                        'How many items they bought',
                        'Total price in pounds',
                        'Coupon code if they used one',
                        'Status — paid, pending, failed, or cancelled',
                        'Date and time',
                    ]],
                    ['type' => 'list', 'title' => 'Important', 'items' => [
                        'You can read orders here. You cannot change them in admin.',
                        'You cannot refund from this page. Use your Stripe dashboard for refunds.',
                        'There is no print or export button yet.',
                    ]],
                ],
            ],
            [
                'id' => 'orders-filter',
                'category' => 'orders',
                'title' => 'Filter orders by status',
                'keywords' => 'filter paid pending failed cancelled status tabs',
                'summary' => 'Use the tabs to see only one type of order.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'Open Orders.',
                        'Tap All, Paid, Pending, Failed, or Cancelled at the top.',
                        'The list updates.',
                    ]],
                    ['type' => 'text', 'text' => 'From Overview you can tap View failed orders to jump straight to failed ones.'],
                ],
            ],
            [
                'id' => 'orders-fulfil',
                'category' => 'orders',
                'title' => 'What to do when you get an order',
                'keywords' => 'fulfil fulfill delivery cook prepare stripe email phone',
                'summary' => 'Use the order details to contact the customer and cook their food.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'Open Orders and find a paid order.',
                        'Note the customer name, phone, and email.',
                        'Stripe also sends you payment emails if it is set up.',
                        'Cook and deliver the food using your normal process.',
                        'Call or email the customer if you need more details.',
                    ]],
                    ['type' => 'text', 'text' => 'The admin does not send cooking instructions to your kitchen. It is a list for you to read.'],
                ],
            ],
            [
                'id' => 'meals-basics',
                'category' => 'meals',
                'title' => 'Meals page basics',
                'keywords' => 'meals menu products food list table',
                'summary' => 'Meals is where you manage everything on the menu.',
                'blocks' => [
                    ['type' => 'text', 'text' => 'Open Meals from the left menu. You see every meal on the shop.'],
                    ['type' => 'list', 'title' => 'The table shows', 'items' => [
                        'A small photo',
                        'Meal name',
                        'Category — soups, rice, platters, and so on',
                        'Price',
                        'Source — built-in (came with the site) or custom (you added it)',
                        'Status — live on the shop or hidden',
                        'Edit and Delete buttons',
                        'A tick box on each row for bulk delete',
                    ]],
                    ['type' => 'text', 'text' => 'Tick meals, then tap Delete selected to remove more than one at once. Tap Add meal to create a new one. Tap Edit to change an existing meal.'],
                ],
            ],
            [
                'id' => 'meals-add',
                'category' => 'meals',
                'title' => 'Add a new meal',
                'keywords' => 'add new meal product create menu item',
                'summary' => 'Use Add meal to put new food on the shop.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'Open Meals.',
                        'Tap Add meal.',
                        'A form window opens.',
                        'Fill in the fields (see other help topics for each field).',
                        'Tap Save meal.',
                        'The meal appears on the shop right away.',
                    ]],
                ],
            ],
            [
                'id' => 'meals-name-description',
                'category' => 'meals',
                'title' => 'Meal name and description',
                'keywords' => 'title name description text bold bullet list format editor',
                'summary' => 'Write what customers read on the product page.',
                'blocks' => [
                    ['type' => 'list', 'title' => 'Meal name', 'items' => [
                        'Required.',
                        'Use a clear name like Jollof Rice with Chicken.',
                    ]],
                    ['type' => 'list', 'title' => 'Description', 'items' => [
                        'Click in the box first, then type.',
                        'Use the toolbar for bold text, bullet lists, and numbered lists.',
                        'Open the style menu on the right to pick Normal text or Heading 1 to Heading 5.',
                        'The menu shows the style for the line your cursor is on.',
                        'Press Enter for a new line.',
                    ]],
                ],
            ],
            [
                'id' => 'meals-photos',
                'category' => 'meals',
                'title' => 'Add photos and videos',
                'keywords' => 'photo image picture video upload media drag drop file jpg png mp4',
                'summary' => 'Upload up to 5 photos or videos per meal.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'Open Add meal or Edit on a meal.',
                        'Find Photos and videos at the top of the form.',
                        'Click the upload box or drag files onto it.',
                        'Wait for each file to finish uploading.',
                        'The first image becomes the cover photo on the menu.',
                        'Tap the X on a file to remove it before you save.',
                        'Tap Save meal when you are done.',
                    ]],
                    ['type' => 'list', 'title' => 'Rules', 'items' => [
                        'Up to 5 files per meal.',
                        'Each file must be 5 MB or smaller.',
                        'Allowed types: JPG, PNG, WebP, GIF, MP4, WebM, MOV.',
                    ]],
                ],
            ],
            [
                'id' => 'meals-category',
                'category' => 'meals',
                'title' => 'Pick a category',
                'keywords' => 'category soups stews rice platters peppered proteins section',
                'summary' => 'Every meal sits in one menu section.',
                'blocks' => [
                    ['type' => 'list', 'title' => 'You can pick from', 'items' => [
                        'Soups',
                        'Stews',
                        'Rice Dishes',
                        'Platters',
                        'Peppered Proteins',
                    ]],
                    ['type' => 'text', 'text' => 'You cannot add new category names in admin. Ask your developer if you need a new section.'],
                ],
            ],
            [
                'id' => 'meals-pricing-litre',
                'category' => 'meals',
                'title' => 'Price by litres (soups and stews)',
                'keywords' => 'price litre liters 2 litres 4 litres soup stew cost',
                'summary' => 'Some meals sell in 2 litre or 4 litre tubs.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'In the form, find Sold by.',
                        'Pick 2 litres / 4 litres.',
                        'Type the price for 2 litres.',
                        'Type the price for 4 litres.',
                        'Save the meal.',
                    ]],
                    ['type' => 'text', 'text' => 'Customers pick the size on the product page before they add to basket.'],
                ],
            ],
            [
                'id' => 'meals-pricing-platter',
                'category' => 'meals',
                'title' => 'Price by tray (platters)',
                'keywords' => 'price tray platter compare discount label sale',
                'summary' => 'Platters sell as one tray with one price.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'In the form, find Sold by.',
                        'Pick 1 tray.',
                        'Type the tray price.',
                        'Optional: type a higher compare-at price to show a was-price.',
                        'Optional: type a short discount label like Save £10.',
                        'Save the meal.',
                    ]],
                ],
            ],
            [
                'id' => 'meals-visibility',
                'category' => 'meals',
                'title' => 'Show or hide a meal on the shop',
                'keywords' => 'hide show live visible active checkbox turn off',
                'summary' => 'Uncheck the box to hide a meal without deleting it.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'Edit the meal.',
                        'Find Show this meal on the shop.',
                        'Tick the box to show it. Untick to hide it.',
                        'Save the meal.',
                    ]],
                    ['type' => 'text', 'text' => 'Hidden meals stay in your admin list but customers cannot buy them.'],
                ],
            ],
            [
                'id' => 'meals-edit-builtin',
                'category' => 'meals',
                'title' => 'Edit a built-in meal',
                'keywords' => 'built-in built in default original override change',
                'summary' => 'Built-in meals came with the site. You can still change them.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Tap Edit on any built-in meal.',
                        'Change name, photos, prices, text, or category.',
                        'Save. Your changes show on the shop.',
                        'The meal is still built-in in the background. You are saving your own version on top.',
                    ]],
                    ['type' => 'list', 'title' => 'If you delete a built-in meal', 'items' => [
                        'It is hidden from the shop.',
                        'It is not removed from the site code.',
                        'You can edit it again later and turn it back on.',
                    ]],
                ],
            ],
            [
                'id' => 'meals-delete-custom',
                'category' => 'meals',
                'title' => 'Delete a meal you added',
                'keywords' => 'delete remove custom permanent forever',
                'summary' => 'Deleting a custom meal removes it for good.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'Find the meal or meals in the list.',
                        'Tick the box on one row, or tick the top box to select all.',
                        'Tap Delete selected, or tap Delete on one row.',
                        'Confirm when the browser asks.',
                        'Custom meals and their photos are removed. Built-in meals are hidden.',
                    ]],
                    ['type' => 'text', 'text' => 'Only custom meals delete forever. Built-in meals only hide.'],
                ],
            ],
            [
                'id' => 'coupons-basics',
                'category' => 'coupons',
                'title' => 'Coupons page basics',
                'keywords' => 'coupon discount code promo offer sale',
                'summary' => 'Coupons give customers a discount when they checkout.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Only one coupon can be live at a time.',
                        'The live coupon shows on your homepage.',
                        'Customers type the code when they pay.',
                        'When you make a new live coupon, the old one pauses.',
                    ]],
                ],
            ],
            [
                'id' => 'coupons-live-preview',
                'category' => 'coupons',
                'title' => 'See the live coupon preview',
                'keywords' => 'preview banner homepage live shop',
                'summary' => 'The top box shows what customers see on the homepage.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'Open Coupons.',
                        'Look at Live on your shop at the top.',
                        'This matches the banner on your homepage.',
                        'Edit the form below to change it.',
                    ]],
                ],
            ],
            [
                'id' => 'coupons-create',
                'category' => 'coupons',
                'title' => 'Make or edit a coupon',
                'keywords' => 'create edit save code percent fixed minimum order expiry',
                'summary' => 'Fill in the coupon form and tap Save.',
                'blocks' => [
                    ['type' => 'list', 'title' => 'Code', 'items' => [
                        'The word customers type at checkout.',
                        'Use letters and numbers. Example: EDDFIRST15.',
                    ]],
                    ['type' => 'list', 'title' => 'Type and value', 'items' => [
                        'Percent off — value is a number like 15 for 15% off.',
                        'Fixed amount off — value is pounds like 5 for £5 off.',
                    ]],
                    ['type' => 'list', 'title' => 'Min order', 'items' => [
                        'Smallest basket total before the code works.',
                        'Use 0 if there is no minimum.',
                    ]],
                    ['type' => 'list', 'title' => 'Homepage banner text', 'items' => [
                        'The sentence next to the code on the homepage.',
                        'Leave blank and the site writes one from your discount.',
                    ]],
                    ['type' => 'list', 'title' => 'Checkout success message', 'items' => [
                        'Shown after the code works.',
                        'Example: We took 15% off your order.',
                    ]],
                    ['type' => 'list', 'title' => 'Expires', 'items' => [
                        'Optional end date.',
                        'Leave blank if it never expires.',
                    ]],
                    ['type' => 'list', 'title' => 'One use per customer email', 'items' => [
                        'Tick this if each person can only use the code once.',
                    ]],
                    ['type' => 'steps', 'title' => 'Save', 'items' => [
                        'Tap Save coupon.',
                        'If this is the live coupon, the shop updates straight away.',
                    ]],
                ],
            ],
            [
                'id' => 'coupons-new',
                'category' => 'coupons',
                'title' => 'Start a brand new coupon',
                'keywords' => 'new coupon replace pause old go live',
                'summary' => 'Use New coupon when you want a fresh code.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'Open Coupons.',
                        'Tap New coupon.',
                        'The form clears.',
                        'Fill in the new code and discount.',
                        'Keep Make this the live coupon ticked.',
                        'Tap Save coupon.',
                        'The old live coupon pauses. The new one goes live.',
                    ]],
                ],
            ],
            [
                'id' => 'coupons-reactivate',
                'category' => 'coupons',
                'title' => 'Turn an old coupon live again',
                'keywords' => 'reactivate go live paused old coupon switch',
                'summary' => 'Use Go live on a paused coupon in the list.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'Scroll to the coupon list at the bottom.',
                        'Find a paused coupon.',
                        'Tap Go live.',
                        'That coupon becomes live. The previous live one pauses.',
                    ]],
                ],
            ],
            [
                'id' => 'coupons-delete',
                'category' => 'coupons',
                'title' => 'Delete a coupon',
                'keywords' => 'delete remove coupon',
                'summary' => 'Delete removes a coupon from the list.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'Find the coupon in the list.',
                        'Tap Delete.',
                        'Confirm when asked.',
                    ]],
                    ['type' => 'text', 'text' => 'Do not delete your only live coupon unless you have another ready.'],
                ],
            ],
            [
                'id' => 'analytics-basics',
                'category' => 'analytics',
                'title' => 'Analytics page basics',
                'keywords' => 'analytics traffic visitors page views sessions',
                'summary' => 'Analytics shows how people use your website.',
                'blocks' => [
                    ['type' => 'list', 'title' => 'You will see', 'items' => [
                        'Page views — how many pages were opened',
                        'Sessions — visits to your site',
                        'Visitors — different people (best guess)',
                        'Bounce rate — people who left after one page',
                        'Pages per session — how many pages per visit',
                        'Charts for traffic over time',
                        'Lists for sources, devices, countries, cities, browsers, and top pages',
                    ]],
                    ['type' => 'text', 'text' => 'People must accept analytics cookies before we count them. That is the law in the UK.'],
                ],
            ],
            [
                'id' => 'analytics-dates',
                'category' => 'analytics',
                'title' => 'Pick dates on Analytics',
                'keywords' => 'analytics date filter custom period',
                'summary' => 'Works the same as Overview date buttons.',
                'blocks' => [
                    ['type' => 'text', 'text' => 'Use Today, 7 days, 30 days, All time, or Custom at the top of the Analytics page.'],
                ],
            ],
            [
                'id' => 'analytics-ga4',
                'category' => 'analytics',
                'title' => 'Google Analytics gender and age',
                'keywords' => 'google analytics ga4 gender age demographics',
                'summary' => 'Extra age and gender data needs Google Analytics set up.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'If you see a setup message, Google Analytics is not connected yet.',
                        'Your developer adds GA4 keys on the server.',
                        'After it is live, gender and age panels fill in when enough people visit.',
                        'This can take days or weeks.',
                    ]],
                ],
            ],
            [
                'id' => 'shop-customer-journey',
                'category' => 'shop',
                'title' => 'What customers can do on the shop',
                'keywords' => 'customer shop browse menu basket checkout order',
                'summary' => 'This is what happens on the public website.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Browse the menu by category',
                        'Open a meal page and read the description',
                        'Pick a size (litres or tray) where needed',
                        'Add food to the basket',
                        'Save items to a wishlist',
                        'Use a coupon code at checkout',
                        'Pay with card through Stripe',
                        'Sign up or sign in with email code',
                        'Book catering for events on the catering page',
                        'Contact you through the contact form',
                        'Join the email deals list',
                        'Read terms, privacy, and cookie policy',
                    ]],
                ],
            ],
            [
                'id' => 'shop-catering',
                'category' => 'shop',
                'title' => 'Catering requests',
                'keywords' => 'catering booking wedding birthday event form',
                'summary' => 'Catering forms send you an email. There is no catering inbox in admin.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Customers fill in the catering form on the catering page.',
                        'You get an email with their details.',
                        'You may also get a WhatsApp message if that is set up.',
                        'Reply to the customer by phone, email, or WhatsApp.',
                        'You cannot read catering forms inside admin.',
                    ]],
                ],
            ],
            [
                'id' => 'shop-cookies',
                'category' => 'shop',
                'title' => 'Cookie banner on the shop',
                'keywords' => 'cookies consent banner gdpr agree decline',
                'summary' => 'Visitors choose cookies before we track them.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'New visitors see a cookie pop-up.',
                        'They can agree, decline, or change preferences.',
                        'Analytics only runs if they agree.',
                        'They can open Cookie settings in the footer later.',
                        'The cookie policy page is at /cookies.php on your site.',
                    ]],
                ],
            ],
            [
                'id' => 'limits-not-in-admin',
                'category' => 'limits',
                'title' => 'Things you cannot change in admin',
                'keywords' => 'cannot change developer code settings env',
                'summary' => 'Some parts of the site need your developer.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Homepage hero text and images',
                        'About page story',
                        'Catering page banners and event types list',
                        'Footer text and legal pages (terms, privacy)',
                        'Menu category names and order',
                        'Site logo file',
                        'Stripe payment keys',
                        'Google Analytics setup',
                        'Owner email list for admin access',
                        'Contact form messages inbox',
                        'Newsletter subscriber list',
                        'Order status changes and refunds',
                    ]],
                    ['type' => 'text', 'text' => 'Email your developer when you need any of these changed.'],
                ],
            ],
            [
                'id' => 'limits-stripe',
                'category' => 'limits',
                'title' => 'Stripe and payments',
                'keywords' => 'stripe payment card refund payout bank',
                'summary' => 'Card payments run through Stripe, not through admin.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Customers pay on the checkout page.',
                        'Money goes to your Stripe account.',
                        'Log in at stripe.com to see payouts and refunds.',
                        'If checkout fails, ask your developer to check Stripe keys.',
                    ]],
                ],
            ],
            [
                'id' => 'limits-add-owner',
                'category' => 'limits',
                'title' => 'Add another admin person',
                'keywords' => 'admin owner email access staff team',
                'summary' => 'New owners need their email added by your developer.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Send your developer the new person\'s email address.',
                        'They add it to the owner list on the server.',
                        'That person can then sign in at admin/signin.php.',
                        'There is no Add user button in admin.',
                    ]],
                ],
            ],
            [
                'id' => 'tips-meal-not-showing',
                'category' => 'help',
                'title' => 'A meal does not show on the shop',
                'keywords' => 'meal missing not showing hidden live problem fix',
                'summary' => 'Check these things first.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Edit the meal. Is Show this meal on the shop ticked?',
                        'Did you tap Save meal after changes?',
                        'Open View shop in a new tab and refresh the page.',
                        'If you deleted a built-in meal, edit it and turn it back on.',
                    ]],
                ],
            ],
            [
                'id' => 'tips-upload-fail',
                'category' => 'help',
                'title' => 'Photo upload fails',
                'keywords' => 'upload error photo image too big file size fail',
                'summary' => 'Usually the file is too big or the wrong type.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Each file must be 5 MB or less.',
                        'You can only upload 5 files per meal.',
                        'Use JPG, PNG, WebP, GIF, MP4, WebM, or MOV.',
                        'Try a smaller image on your phone or computer first.',
                    ]],
                ],
            ],
            [
                'id' => 'tips-coupon-not-working',
                'category' => 'help',
                'title' => 'Coupon does not work at checkout',
                'keywords' => 'coupon code invalid not working checkout error',
                'summary' => 'Check the coupon rules.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Is the coupon live? Only one live coupon works on the homepage banner.',
                        'Did the customer type the code exactly? Codes are not case sensitive but must match.',
                        'Is the basket big enough for the minimum order?',
                        'Did this email already use a one-time coupon?',
                        'Has the expiry date passed?',
                    ]],
                ],
            ],
            [
                'id' => 'tips-no-revenue',
                'category' => 'help',
                'title' => 'Overview shows zero revenue',
                'keywords' => 'revenue zero no sales stripe empty dashboard',
                'summary' => 'No paid orders yet, or Stripe is not connected.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Pick a longer date range like All time.',
                        'Check Orders for any paid rows.',
                        'If every order is pending or failed, payments are not finishing.',
                        'Ask your developer to check Stripe is set up on the live site.',
                    ]],
                ],
            ],
            [
                'id' => 'tips-sign-in-fail',
                'category' => 'help',
                'title' => 'Cannot sign in to admin',
                'keywords' => 'sign in login fail code email otp wrong',
                'summary' => 'Try these steps.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Use the same email your developer put on the owner list.',
                        'Check spam for the 6-digit code.',
                        'Wait 30 seconds before you tap Resend code.',
                        'Codes expire after about 10 minutes. Request a new one.',
                        'If you are signed in as a customer, tap Sign out of customer account on the sign-in page.',
                    ]],
                ],
            ],
            [
                'id' => 'tips-view-shop',
                'category' => 'help',
                'title' => 'See changes on the live shop',
                'keywords' => 'preview view shop refresh cache changes live',
                'summary' => 'Always check the shop after you save.',
                'blocks' => [
                    ['type' => 'steps', 'items' => [
                        'Tap View shop in the left menu.',
                        'A new tab opens on the menu page.',
                        'Refresh the page if you do not see changes.',
                        'On mobile, close the tab and open it again.',
                    ]],
                    ['type' => 'text', 'text' => 'Meal and coupon changes go live right away. No extra publish button.'],
                ],
            ],
            [
                'id' => 'contact-developer',
                'category' => 'help',
                'title' => 'When to contact your developer',
                'keywords' => 'developer help support contact stuck broken',
                'summary' => 'Call your developer when admin cannot fix it.',
                'blocks' => [
                    ['type' => 'list', 'items' => [
                        'Checkout or Stripe errors keep happening',
                        'You need a new menu category',
                        'You want homepage or about page text changed',
                        'Google Analytics never connects',
                        'You need a new admin email added',
                        'The whole admin area will not load',
                        'You want a new feature that is not in this manual',
                    ]],
                ],
            ],
        ],
    ];
}

/**
 * @return list<array{id: string, label: string, icon: string}>
 */
function get_admin_help_categories(): array
{
    return get_admin_help_manual()['categories'];
}

/**
 * @return list<array<string, mixed>>
 */
function get_admin_help_articles(): array
{
    return get_admin_help_manual()['articles'];
}
