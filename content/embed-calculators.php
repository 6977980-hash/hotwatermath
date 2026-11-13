<?php
/**
 * /embed-calculators/ (PHASES Phase 8 task 3): how to embed our calculators. WordPress reserves the page slug
 * "embed", so the landing page lives here and the iframes stay at /embed/<tool>/ (docs/DECISIONS.md).
 * Rules (CLAUDE.md §7b): brand-name credit only, rel="nofollow", removable, one link, never traded for a link.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_tools  = hwm_core_tools();
$hwm_brands = array_values( array_filter( hwm_pages_of( 'tool', array( 'live' ) ), static fn( $e ) => ! empty( $e['parent'] ) ) );

$related = array( 'calculators', 'data', 'water-heater-installation-cost-index', 'methodology', 'about' );
?>
<h2>How do I embed a calculator?</h2>
<p>Pick a calculator below, copy its code and paste it into your page wherever HTML is allowed (a "Custom HTML" block in WordPress, an embed block in Squarespace or Wix). The calculator loads in a frame, works on phones, and gives the same results as on our site.</p>
<ol>
	<li>Find the calculator you want in the list below.</li>
	<li>Click into its code box and copy everything.</li>
	<li>Paste it into your page as HTML. Change <code>height="640"</code> if your layout needs more or less room.</li>
</ol>

<h2>What are the rules?</h2>
<p>There are only a few, and none of them asks anything of you in return.</p>
<ul>
	<li><strong>Free, for any site.</strong> Personal blogs, inspectors, utilities, schools and newsrooms are all welcome.</li>
	<li><strong>No ads inside.</strong> The embedded calculator shows no advertising and no pop-ups.</li>
	<li><strong>The credit line is optional.</strong> Under the frame the code adds one small line, "Calculator by <?php echo esc_html( (string) hwm_site( 'brand' ) ); ?>", with a <code>nofollow</code> link. You may delete it. We never ask for a link in exchange for the calculator.</li>
	<li><strong>Same numbers as our site.</strong> Formulas and sources are public on <a href="<?php echo esc_url( hwm_url( 'methodology' ) ); ?>">our methodology page</a>, so you can check what your readers see.</li>
</ul>
<dl>
	<dt>iframe</dt>
	<dd>A frame that shows one web page inside another. The calculator runs on our server inside your page, so you never have to update it: when we fix or update a formula, your copy updates too.</dd>
</dl>

<h2>Which calculators can I embed?</h2>
<?php foreach ( $hwm_tools as $hwm_tool ) : ?>
	<section class="dataset" id="<?php echo esc_attr( 'embed-' . $hwm_tool['slug'] ); ?>" aria-labelledby="<?php echo esc_attr( 'h-' . $hwm_tool['slug'] ); ?>">
		<h3 id="<?php echo esc_attr( 'h-' . $hwm_tool['slug'] ); ?>"><?php echo esc_html( (string) $hwm_tool['title'] ); ?></h3>
		<p><?php echo esc_html( (string) ( $hwm_tool['summary'] ?? '' ) ); ?> <a href="<?php echo esc_url( hwm_origin() . '/embed/' . $hwm_tool['slug'] . '/' ); ?>" rel="nofollow">Preview the embed</a> or <?php echo hwm_link( (string) $hwm_tool['slug'], 'open the full page' ); // phpcs:ignore ?>.</p>
		<textarea readonly rows="5" aria-label="<?php echo esc_attr( 'Embed code: ' . $hwm_tool['title'] ); ?>"><?php echo esc_textarea( hwm_embed_snippet( (string) $hwm_tool['slug'] ) ); ?></textarea>
	</section>
<?php endforeach; ?>
<?php if ( $hwm_brands ) : ?>
	<p>The brand serial-number decoders can be embedded the same way; each one opens preset to its brand:</p>
	<ul>
		<?php foreach ( $hwm_brands as $hwm_b ) : ?>
			<li><?php echo esc_html( (string) $hwm_b['title'] ); ?>: <code><?php echo esc_html( hwm_origin() . '/embed/' . $hwm_b['slug'] . '/' ); ?></code></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<h2>Does the embed track my visitors?</h2>
<p>The frame counts how many times it is viewed, and on which site, with Google Analytics, under the same consent settings as our own pages: for visitors in the EU, UK and Switzerland, analytics storage is off by default. The frame loads no advertising code. Details are in our <?php echo hwm_link( 'privacy-policy', 'privacy policy' ); // phpcs:ignore ?>.</p>

<h2>Can I embed a chart or a table instead?</h2>
<p>Yes. The <?php echo hwm_link( 'water-heater-installation-cost-index', 'water heater cost index' ); // phpcs:ignore ?> has its own chart embed code, and every table our tools use is a free CSV on the <?php echo hwm_link( 'data', 'data downloads page' ); // phpcs:ignore ?> under CC BY 4.0.</p>
<?php
$faq = array(
	array(
		'q' => 'Is it free to embed a Hot Water Math calculator?',
		'a' => 'Yes, for any site, including commercial ones. There is no sign-up and no fee, and we never ask for a link in return.',
	),
	array(
		'q' => 'Do I have to keep the credit link?',
		'a' => 'No. The credit line under the frame is optional and carries a nofollow link. You may remove it, and the calculator keeps working.',
	),
	array(
		'q' => 'Will the embedded calculator show ads?',
		'a' => 'No. Embedded calculators never show advertising or pop-ups, only the calculator and its result.',
	),
	array(
		'q' => 'What if the frame is too short on my page?',
		'a' => 'Change the height value in the code, for example to 800. The calculator inside adapts to the frame width, so it also fits narrow sidebars and phones.',
	),
);
