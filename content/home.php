<?php
/**
 * Temporary home page (Phase 0): what the site does, the tools being built, and how numbers are made.
 * Phase 5 replaces this with the launch home page.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$hwm_tools    = hwm_pages_of( 'tool', array( 'live', 'draft', 'planned' ) );
$hwm_articles = hwm_pages_of( 'article', array( 'live', 'draft', 'planned' ) );
?>
<h2>What you can work out here</h2>
<p>Each calculator answers one question a homeowner has when a water heater needs replacing or is being chosen. They are being built and checked one at a time, and each goes up only after its formulas and sources are verified.</p>
<ul class="cards">
	<?php foreach ( $hwm_tools as $hwm_p ) : ?>
		<li class="card<?php echo hwm_is_live( $hwm_p ) ? '' : ' card--muted'; ?>">
			<?php if ( ! hwm_is_live( $hwm_p ) ) : ?><span class="badge">Coming soon</span><?php endif; ?>
			<h3>
				<?php if ( hwm_is_live( $hwm_p ) ) : ?>
					<a href="<?php echo esc_url( hwm_url( $hwm_p['slug'] ) ); ?>"><?php echo esc_html( (string) $hwm_p['title'] ); ?></a>
				<?php else : ?>
					<?php echo esc_html( (string) $hwm_p['title'] ); ?>
				<?php endif; ?>
			</h3>
			<p><?php echo esc_html( (string) ( $hwm_p['summary'] ?? '' ) ); ?></p>
		</li>
	<?php endforeach; ?>
</ul>

<h2>How our numbers are made</h2>
<p>A calculator is only as good as the numbers inside it, so we show ours.</p>
<dl>
	<dt>Published formulas</dt>
	<dd>Every result comes with a "How we calculated this" panel that shows the formula with your own inputs. The full set is on <a href="<?php echo esc_url( hwm_url( 'methodology' ) ); ?>">our methodology page</a>.</dd>
	<dt>Named sources</dt>
	<dd>Default values such as fixture flow rates, groundwater temperatures and energy prices come from primary sources like the U.S. Department of Energy, ENERGY STAR and the Energy Information Administration, with the date we checked them.</dd>
	<dt>Checked against manufacturers</dt>
	<dd>For each sizing tool we run test cases and compare our answer with the manufacturers' own sizing guides, and we publish the differences.</dd>
	<dt>No hidden sales</dt>
	<dd>We are not plumbers and we do not refer contractors or sell leads. Read <a href="<?php echo esc_url( hwm_url( 'about' ) ); ?>">who builds this site</a> and <a href="<?php echo esc_url( hwm_url( 'editorial-policy' ) ); ?>">our editorial policy</a>.</dd>
</dl>

<?php if ( $hwm_articles ) : ?>
	<h2>Guides on the way</h2>
	<ul>
		<?php foreach ( $hwm_articles as $hwm_p ) : ?>
			<li><?php echo hwm_is_live( $hwm_p ) ? '<a href="' . esc_url( hwm_url( $hwm_p['slug'] ) ) . '">' . esc_html( (string) $hwm_p['title'] ) . '</a>' : esc_html( (string) $hwm_p['title'] ); ?>: <?php echo esc_html( (string) ( $hwm_p['summary'] ?? '' ) ); ?></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>

<h2>Who this site is for</h2>
<p>Homeowners in the United States who are replacing a water heater, choosing between tank and tankless, or trying to tell how old their current unit is. DIYers and small contractors who want to check a number quickly are welcome too.</p>
<p>All results are planning estimates. Gas, venting and electrical work have code and safety rules that vary by town, so confirm the final choice with a licensed professional in your area.</p>
