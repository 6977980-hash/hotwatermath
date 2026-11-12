/**
 * The 50 states + DC used by every state-keyed data file (scripts/validate-data.mjs checks all 51 are present).
 * noaa: NOAA Climate at a Glance series. "statewide/<n>" uses the statewide average (contiguous states and Alaska's
 * statewide series exist; NOAA publishes no statewide series for Hawaii or DC). For Alaska, Hawaii and DC a city
 * series is used instead, because the statewide Alaska average (mostly unpopulated interior) is far colder than
 * where most Alaskans live. See data/groundwater-temp-by-state.json meta.notes.
 */
export const STATES = [
	['AL', 'Alabama', 'statewide/1'], ['AK', 'Alaska', 'city/USW00026451'], ['AZ', 'Arizona', 'statewide/2'],
	['AR', 'Arkansas', 'statewide/3'], ['CA', 'California', 'statewide/4'], ['CO', 'Colorado', 'statewide/5'],
	['CT', 'Connecticut', 'statewide/6'], ['DE', 'Delaware', 'statewide/7'], ['DC', 'District of Columbia', 'city/USW00013743'],
	['FL', 'Florida', 'statewide/8'], ['GA', 'Georgia', 'statewide/9'], ['HI', 'Hawaii', 'city/USW00022521'],
	['ID', 'Idaho', 'statewide/10'], ['IL', 'Illinois', 'statewide/11'], ['IN', 'Indiana', 'statewide/12'],
	['IA', 'Iowa', 'statewide/13'], ['KS', 'Kansas', 'statewide/14'], ['KY', 'Kentucky', 'statewide/15'],
	['LA', 'Louisiana', 'statewide/16'], ['ME', 'Maine', 'statewide/17'], ['MD', 'Maryland', 'statewide/18'],
	['MA', 'Massachusetts', 'statewide/19'], ['MI', 'Michigan', 'statewide/20'], ['MN', 'Minnesota', 'statewide/21'],
	['MS', 'Mississippi', 'statewide/22'], ['MO', 'Missouri', 'statewide/23'], ['MT', 'Montana', 'statewide/24'],
	['NE', 'Nebraska', 'statewide/25'], ['NV', 'Nevada', 'statewide/26'], ['NH', 'New Hampshire', 'statewide/27'],
	['NJ', 'New Jersey', 'statewide/28'], ['NM', 'New Mexico', 'statewide/29'], ['NY', 'New York', 'statewide/30'],
	['NC', 'North Carolina', 'statewide/31'], ['ND', 'North Dakota', 'statewide/32'], ['OH', 'Ohio', 'statewide/33'],
	['OK', 'Oklahoma', 'statewide/34'], ['OR', 'Oregon', 'statewide/35'], ['PA', 'Pennsylvania', 'statewide/36'],
	['RI', 'Rhode Island', 'statewide/37'], ['SC', 'South Carolina', 'statewide/38'], ['SD', 'South Dakota', 'statewide/39'],
	['TN', 'Tennessee', 'statewide/40'], ['TX', 'Texas', 'statewide/41'], ['UT', 'Utah', 'statewide/42'],
	['VT', 'Vermont', 'statewide/43'], ['VA', 'Virginia', 'statewide/44'], ['WA', 'Washington', 'statewide/45'],
	['WV', 'West Virginia', 'statewide/46'], ['WI', 'Wisconsin', 'statewide/47'], ['WY', 'Wyoming', 'statewide/48'],
].map(([code, name, noaa]) => ({ code, name, noaa }));

export const STATE_CODES = STATES.map((s) => s.code);
