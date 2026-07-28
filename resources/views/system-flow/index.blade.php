<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">System Flow</h2>
            <p class="mt-1 text-sm text-slate-500">Patient journey through stations — flowchart and clinic floor plan</p>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8" x-data="{ view: 'flowchart' }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="inline-flex rounded-lg border border-slate-200 bg-white p-1 shadow-sm">
                <button
                    type="button"
                    @click="view = 'flowchart'"
                    :class="view === 'flowchart' ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-slate-50'"
                    class="px-4 py-2 text-sm font-medium rounded-md transition"
                >
                    Flowchart
                </button>
                <button
                    type="button"
                    @click="view = 'floorplan'"
                    :class="view === 'floorplan' ? 'bg-teal-700 text-white' : 'text-slate-600 hover:bg-slate-50'"
                    class="px-4 py-2 text-sm font-medium rounded-md transition"
                >
                    Floor Plan
                </button>
            </div>

            {{-- Flowchart --}}
            <div x-show="view === 'flowchart'" x-cloak class="bg-white shadow-sm rounded-lg border border-slate-200 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-100">
                    <h3 class="text-base font-semibold text-slate-800">Patient process flowchart</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Decision points for consultation and pharmacy referral</p>
                </div>
                <div class="p-4 sm:p-6 overflow-x-auto">
                    <svg viewBox="0 0 640 1120" class="w-full min-w-[520px] h-auto mx-auto" role="img" aria-label="Patient system flowchart">
                        <defs>
                            <marker id="arrow" markerWidth="8" markerHeight="8" refX="6" refY="3" orient="auto">
                                <path d="M0,0 L6,3 L0,6 Z" fill="#64748b" />
                            </marker>
                            <filter id="shadow" x="-4%" y="-4%" width="108%" height="112%">
                                <feDropShadow dx="0" dy="1" stdDeviation="1.5" flood-color="#0f172a" flood-opacity="0.08" />
                            </filter>
                        </defs>

                        {{-- 1. Registration --}}
                        <rect x="220" y="20" width="200" height="48" rx="24" fill="#0f766e" filter="url(#shadow)" />
                        <text x="320" y="49" text-anchor="middle" fill="#fff" font-size="14" font-weight="600" font-family="system-ui,sans-serif">1. Registration</text>
                        <line x1="320" y1="68" x2="320" y2="100" stroke="#64748b" stroke-width="2" marker-end="url(#arrow)" />

                        {{-- 2. Vitals --}}
                        <rect x="190" y="100" width="260" height="64" rx="10" fill="#f0fdfa" stroke="#0d9488" stroke-width="2" filter="url(#shadow)" />
                        <text x="320" y="127" text-anchor="middle" fill="#134e4a" font-size="14" font-weight="600" font-family="system-ui,sans-serif">2. Vitals</text>
                        <text x="320" y="148" text-anchor="middle" fill="#0f766e" font-size="12" font-family="system-ui,sans-serif">Part 1 &amp; Part 2</text>
                        <line x1="320" y1="164" x2="320" y2="196" stroke="#64748b" stroke-width="2" marker-end="url(#arrow)" />

                        {{-- 3. Blood Glucose --}}
                        <rect x="190" y="196" width="260" height="52" rx="10" fill="#f0fdfa" stroke="#0d9488" stroke-width="2" filter="url(#shadow)" />
                        <text x="320" y="227" text-anchor="middle" fill="#134e4a" font-size="14" font-weight="600" font-family="system-ui,sans-serif">3. Blood Glucose</text>
                        <line x1="320" y1="248" x2="320" y2="290" stroke="#64748b" stroke-width="2" marker-end="url(#arrow)" />

                        {{-- 4. Decision: Proceed to consultation --}}
                        <polygon points="320,290 430,360 320,430 210,360" fill="#fff7ed" stroke="#ea580c" stroke-width="2" filter="url(#shadow)" />
                        <text x="320" y="348" text-anchor="middle" fill="#9a3412" font-size="12" font-weight="600" font-family="system-ui,sans-serif">4. Encoder</text>
                        <text x="320" y="366" text-anchor="middle" fill="#9a3412" font-size="12" font-family="system-ui,sans-serif">Proceed to</text>
                        <text x="320" y="382" text-anchor="middle" fill="#9a3412" font-size="12" font-family="system-ui,sans-serif">Consultation?</text>

                        {{-- Yes → Consultation --}}
                        <line x1="430" y1="360" x2="500" y2="360" stroke="#64748b" stroke-width="2" marker-end="url(#arrow)" />
                        <text x="455" y="350" fill="#15803d" font-size="12" font-weight="700" font-family="system-ui,sans-serif">Yes</text>
                        <rect x="500" y="328" width="120" height="64" rx="10" fill="#ecfdf5" stroke="#16a34a" stroke-width="2" filter="url(#shadow)" />
                        <text x="560" y="355" text-anchor="middle" fill="#14532d" font-size="12" font-weight="600" font-family="system-ui,sans-serif">5. Consultation</text>
                        <text x="560" y="373" text-anchor="middle" fill="#15803d" font-size="11" font-family="system-ui,sans-serif">Queue &amp; consult</text>

                        {{-- Disposition --}}
                        <line x1="560" y1="392" x2="560" y2="450" stroke="#64748b" stroke-width="2" marker-end="url(#arrow)" />
                        <rect x="480" y="450" width="160" height="56" rx="10" fill="#f8fafc" stroke="#64748b" stroke-width="2" filter="url(#shadow)" />
                        <text x="560" y="473" text-anchor="middle" fill="#334155" font-size="12" font-weight="600" font-family="system-ui,sans-serif">Disposition set</text>
                        <text x="560" y="491" text-anchor="middle" fill="#64748b" font-size="11" font-family="system-ui,sans-serif">Removed from queue</text>

                        {{-- Medicine referred? --}}
                        <line x1="560" y1="506" x2="560" y2="560" stroke="#64748b" stroke-width="2" marker-end="url(#arrow)" />
                        <polygon points="560,560 640,620 560,680 480,620" fill="#eff6ff" stroke="#2563eb" stroke-width="2" filter="url(#shadow)" />
                        <text x="560" y="612" text-anchor="middle" fill="#1e40af" font-size="11" font-weight="600" font-family="system-ui,sans-serif">Medicine</text>
                        <text x="560" y="628" text-anchor="middle" fill="#1e40af" font-size="11" font-family="system-ui,sans-serif">referred?</text>

                        {{-- Yes medicine → Pharmacy (merge center) --}}
                        <line x1="480" y1="620" x2="320" y2="620" stroke="#64748b" stroke-width="2" />
                        <line x1="320" y1="620" x2="320" y2="760" stroke="#64748b" stroke-width="2" marker-end="url(#arrow)" />
                        <text x="400" y="608" fill="#15803d" font-size="12" font-weight="700" font-family="system-ui,sans-serif">Yes</text>

                        {{-- No medicine → Exit --}}
                        <line x1="640" y1="620" x2="620" y2="620" stroke="#64748b" stroke-width="2" />
                        <path d="M620 620 L620 980 L420 980" fill="none" stroke="#64748b" stroke-width="2" marker-end="url(#arrow)" />
                        <text x="600" y="608" fill="#b91c1c" font-size="12" font-weight="700" font-family="system-ui,sans-serif">No</text>

                        {{-- No to consultation --}}
                        <line x1="210" y1="360" x2="120" y2="360" stroke="#64748b" stroke-width="2" marker-end="url(#arrow)" />
                        <text x="155" y="350" fill="#b91c1c" font-size="12" font-weight="700" font-family="system-ui,sans-serif">No</text>
                        <polygon points="120,290 175,360 120,430 65,360" fill="#fef2f2" stroke="#e11d48" stroke-width="2" filter="url(#shadow)" />
                        <text x="120" y="348" text-anchor="middle" fill="#9f1239" font-size="11" font-weight="600" font-family="system-ui,sans-serif">Pharmacy</text>
                        <text x="120" y="364" text-anchor="middle" fill="#9f1239" font-size="11" font-family="system-ui,sans-serif">or Exit?</text>

                        {{-- To pharmacy --}}
                        <line x1="120" y1="430" x2="120" y2="792" stroke="#64748b" stroke-width="2" />
                        <line x1="120" y1="792" x2="190" y2="792" stroke="#64748b" stroke-width="2" marker-end="url(#arrow)" />
                        <text x="78" y="600" fill="#15803d" font-size="11" font-weight="700" font-family="system-ui,sans-serif">Pharmacy</text>

                        {{-- Direct exit --}}
                        <line x1="65" y1="360" x2="30" y2="360" stroke="#64748b" stroke-width="2" />
                        <path d="M30 360 L30 980 L220 980" fill="none" stroke="#64748b" stroke-width="2" marker-end="url(#arrow)" />
                        <text x="42" y="350" fill="#b91c1c" font-size="11" font-weight="700" font-family="system-ui,sans-serif">Exit</text>

                        {{-- 6. Pharmacy --}}
                        <rect x="190" y="760" width="260" height="64" rx="10" fill="#faf5ff" stroke="#7c3aed" stroke-width="2" filter="url(#shadow)" />
                        <text x="320" y="787" text-anchor="middle" fill="#5b21b6" font-size="14" font-weight="600" font-family="system-ui,sans-serif">6. Pharmacy</text>
                        <text x="320" y="807" text-anchor="middle" fill="#7c3aed" font-size="12" font-family="system-ui,sans-serif">Dispense referred medicines</text>
                        <line x1="320" y1="824" x2="320" y2="930" stroke="#64748b" stroke-width="2" marker-end="url(#arrow)" />

                        {{-- 7. Exit --}}
                        <rect x="220" y="930" width="200" height="48" rx="24" fill="#334155" filter="url(#shadow)" />
                        <text x="320" y="959" text-anchor="middle" fill="#fff" font-size="14" font-weight="600" font-family="system-ui,sans-serif">7. Exit</text>
                    </svg>
                </div>

                <div class="px-4 sm:px-6 py-4 border-t border-slate-100 bg-slate-50">
                    <div class="flex flex-wrap gap-4 text-xs text-slate-600">
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-teal-700"></span> Process step</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rotate-45 bg-orange-100 border border-orange-500"></span> Decision</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-violet-100 border border-violet-600"></span> Pharmacy</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-slate-700"></span> Start / Exit</span>
                    </div>
                </div>
            </div>

            {{-- Floor Plan --}}
            <div x-show="view === 'floorplan'" x-cloak class="bg-white shadow-sm rounded-lg border border-slate-200 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-slate-100">
                    <h3 class="text-base font-semibold text-slate-800">Clinic floor plan</h3>
                    <p class="text-sm text-slate-500 mt-0.5">Same journey laid out as station rooms along the patient path</p>
                </div>
                <div class="p-4 sm:p-6 overflow-x-auto bg-slate-100">
                    <svg viewBox="0 0 960 640" class="w-full min-w-[700px] h-auto mx-auto" role="img" aria-label="Clinic floor plan patient flow">
                        <defs>
                            <marker id="pathArrow" markerWidth="9" markerHeight="9" refX="7" refY="3.5" orient="auto">
                                <path d="M0,0 L7,3.5 L0,7 Z" fill="#0f766e" />
                            </marker>
                            <marker id="pathArrowGreen" markerWidth="9" markerHeight="9" refX="7" refY="3.5" orient="auto">
                                <path d="M0,0 L7,3.5 L0,7 Z" fill="#16a34a" />
                            </marker>
                            <marker id="pathArrowViolet" markerWidth="9" markerHeight="9" refX="7" refY="3.5" orient="auto">
                                <path d="M0,0 L7,3.5 L0,7 Z" fill="#7c3aed" />
                            </marker>
                            <marker id="pathArrowSlate" markerWidth="9" markerHeight="9" refX="7" refY="3.5" orient="auto">
                                <path d="M0,0 L7,3.5 L0,7 Z" fill="#475569" />
                            </marker>
                            <marker id="pathArrowOrange" markerWidth="9" markerHeight="9" refX="7" refY="3.5" orient="auto">
                                <path d="M0,0 L7,3.5 L0,7 Z" fill="#ea580c" />
                            </marker>
                            <pattern id="floorTile" width="24" height="24" patternUnits="userSpaceOnUse">
                                <path d="M 24 0 L 0 0 0 24" fill="none" stroke="#e2e8f0" stroke-width="1" />
                            </pattern>
                            <filter id="roomShadow" x="-3%" y="-3%" width="106%" height="110%">
                                <feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="#0f172a" flood-opacity="0.1" />
                            </filter>
                        </defs>

                        {{-- Building --}}
                        <rect x="24" y="24" width="912" height="592" rx="8" fill="#fff" stroke="#94a3b8" stroke-width="3" />
                        <rect x="24" y="24" width="912" height="592" rx="8" fill="url(#floorTile)" opacity="0.45" />
                        <text x="480" y="318" text-anchor="middle" fill="#94a3b8" font-size="13" font-weight="600" letter-spacing="3" font-family="system-ui,sans-serif">MAIN CORRIDOR</text>

                        {{-- Doors --}}
                        <rect x="40" y="280" width="28" height="80" fill="#cbd5e1" stroke="#64748b" stroke-width="2" />
                        <text x="54" y="270" text-anchor="middle" fill="#475569" font-size="10" font-weight="600" font-family="system-ui,sans-serif">IN</text>
                        <rect x="892" y="280" width="28" height="80" fill="#334155" stroke="#1e293b" stroke-width="2" />
                        <text x="906" y="270" text-anchor="middle" fill="#475569" font-size="10" font-weight="600" font-family="system-ui,sans-serif">OUT</text>

                        {{-- 1 Registration --}}
                        <g filter="url(#roomShadow)">
                            <rect x="90" y="60" width="150" height="130" rx="4" fill="#f0fdfa" stroke="#0f766e" stroke-width="2.5" />
                            <rect x="90" y="60" width="150" height="28" fill="#0f766e" />
                            <text x="165" y="79" text-anchor="middle" fill="#fff" font-size="12" font-weight="700" font-family="system-ui,sans-serif">1 · Registration</text>
                            <text x="165" y="120" text-anchor="middle" fill="#134e4a" font-size="11" font-family="system-ui,sans-serif">Patient registers</text>
                            <text x="165" y="138" text-anchor="middle" fill="#0f766e" font-size="10" font-family="system-ui,sans-serif">Create / check-in</text>
                            <rect x="120" y="150" width="90" height="22" rx="2" fill="#99f6e4" stroke="#0d9488" stroke-width="1" />
                        </g>
                        <rect x="145" y="186" width="40" height="8" fill="#fff" stroke="#0f766e" stroke-width="1.5" />

                        {{-- 2 Vitals --}}
                        <g filter="url(#roomShadow)">
                            <rect x="280" y="60" width="150" height="130" rx="4" fill="#ecfeff" stroke="#0891b2" stroke-width="2.5" />
                            <rect x="280" y="60" width="150" height="28" fill="#0891b2" />
                            <text x="355" y="79" text-anchor="middle" fill="#fff" font-size="12" font-weight="700" font-family="system-ui,sans-serif">2 · Vitals</text>
                            <text x="355" y="118" text-anchor="middle" fill="#164e63" font-size="11" font-family="system-ui,sans-serif">Part 1 &amp; Part 2</text>
                            <text x="355" y="136" text-anchor="middle" fill="#0891b2" font-size="10" font-family="system-ui,sans-serif">BP · BMI · height</text>
                            <circle cx="320" cy="162" r="10" fill="#a5f3fc" stroke="#0891b2" stroke-width="1" />
                            <circle cx="355" cy="162" r="10" fill="#a5f3fc" stroke="#0891b2" stroke-width="1" />
                            <circle cx="390" cy="162" r="10" fill="#a5f3fc" stroke="#0891b2" stroke-width="1" />
                        </g>
                        <rect x="335" y="186" width="40" height="8" fill="#fff" stroke="#0891b2" stroke-width="1.5" />

                        {{-- 3 Blood Glucose --}}
                        <g filter="url(#roomShadow)">
                            <rect x="470" y="60" width="150" height="130" rx="4" fill="#fff7ed" stroke="#ea580c" stroke-width="2.5" />
                            <rect x="470" y="60" width="150" height="28" fill="#ea580c" />
                            <text x="545" y="79" text-anchor="middle" fill="#fff" font-size="12" font-weight="700" font-family="system-ui,sans-serif">3 · Blood Glucose</text>
                            <text x="545" y="120" text-anchor="middle" fill="#9a3412" font-size="11" font-family="system-ui,sans-serif">Glucose reading</text>
                            <text x="545" y="138" text-anchor="middle" fill="#c2410c" font-size="10" font-family="system-ui,sans-serif">Encoder station</text>
                            <rect x="515" y="152" width="60" height="20" rx="2" fill="#ffedd5" stroke="#ea580c" stroke-width="1" />
                        </g>
                        <rect x="525" y="186" width="40" height="8" fill="#fff" stroke="#ea580c" stroke-width="1.5" />

                        {{-- 4 Assessment --}}
                        <g filter="url(#roomShadow)">
                            <rect x="660" y="80" width="140" height="100" rx="4" fill="#fffbeb" stroke="#d97706" stroke-width="2" stroke-dasharray="6 3" />
                            <text x="730" y="115" text-anchor="middle" fill="#92400e" font-size="11" font-weight="700" font-family="system-ui,sans-serif">4 · Assessment</text>
                            <text x="730" y="135" text-anchor="middle" fill="#b45309" font-size="10" font-family="system-ui,sans-serif">Proceed to</text>
                            <text x="730" y="150" text-anchor="middle" fill="#b45309" font-size="10" font-family="system-ui,sans-serif">Consultation?</text>
                        </g>

                        {{-- 5 Consultation --}}
                        <g filter="url(#roomShadow)">
                            <rect x="660" y="380" width="220" height="200" rx="4" fill="#ecfdf5" stroke="#16a34a" stroke-width="2.5" />
                            <rect x="660" y="380" width="220" height="28" fill="#16a34a" />
                            <text x="770" y="399" text-anchor="middle" fill="#fff" font-size="12" font-weight="700" font-family="system-ui,sans-serif">5 · Consultation</text>
                            <text x="770" y="445" text-anchor="middle" fill="#14532d" font-size="11" font-family="system-ui,sans-serif">Patient queue</text>
                            <text x="770" y="465" text-anchor="middle" fill="#14532d" font-size="11" font-family="system-ui,sans-serif">Consult → Disposition</text>
                            <rect x="690" y="490" width="28" height="22" rx="2" fill="#bbf7d0" stroke="#16a34a" />
                            <rect x="728" y="490" width="28" height="22" rx="2" fill="#bbf7d0" stroke="#16a34a" />
                            <rect x="766" y="490" width="28" height="22" rx="2" fill="#bbf7d0" stroke="#16a34a" />
                            <rect x="804" y="490" width="28" height="22" rx="2" fill="#bbf7d0" stroke="#16a34a" />
                            <rect x="720" y="535" width="100" height="28" rx="2" fill="#86efac" stroke="#16a34a" />
                            <text x="770" y="553" text-anchor="middle" fill="#14532d" font-size="10" font-family="system-ui,sans-serif">Consultant desk</text>
                        </g>
                        <rect x="740" y="376" width="40" height="8" fill="#fff" stroke="#16a34a" stroke-width="1.5" />

                        {{-- 6 Pharmacy --}}
                        <g filter="url(#roomShadow)">
                            <rect x="280" y="400" width="280" height="180" rx="4" fill="#faf5ff" stroke="#7c3aed" stroke-width="2.5" />
                            <rect x="280" y="400" width="280" height="28" fill="#7c3aed" />
                            <text x="420" y="419" text-anchor="middle" fill="#fff" font-size="12" font-weight="700" font-family="system-ui,sans-serif">6 · Pharmacy</text>
                            <text x="420" y="470" text-anchor="middle" fill="#5b21b6" font-size="11" font-family="system-ui,sans-serif">Dispense referred medicines</text>
                            <rect x="310" y="490" width="50" height="60" rx="2" fill="#ede9fe" stroke="#7c3aed" />
                            <rect x="375" y="490" width="50" height="60" rx="2" fill="#ede9fe" stroke="#7c3aed" />
                            <rect x="440" y="490" width="50" height="60" rx="2" fill="#ede9fe" stroke="#7c3aed" />
                            <rect x="505" y="500" width="30" height="40" rx="2" fill="#ddd6fe" stroke="#7c3aed" />
                        </g>
                        <rect x="400" y="396" width="40" height="8" fill="#fff" stroke="#7c3aed" stroke-width="1.5" />

                        {{-- 7 Exit --}}
                        <g filter="url(#roomShadow)">
                            <rect x="90" y="420" width="140" height="140" rx="4" fill="#f8fafc" stroke="#64748b" stroke-width="2" />
                            <rect x="90" y="420" width="140" height="28" fill="#475569" />
                            <text x="160" y="439" text-anchor="middle" fill="#fff" font-size="12" font-weight="700" font-family="system-ui,sans-serif">7 · Exit</text>
                            <text x="160" y="490" text-anchor="middle" fill="#334155" font-size="11" font-family="system-ui,sans-serif">Patient leaves</text>
                            <text x="160" y="510" text-anchor="middle" fill="#64748b" font-size="10" font-family="system-ui,sans-serif">Visit complete</text>
                        </g>
                        <rect x="135" y="416" width="40" height="8" fill="#fff" stroke="#64748b" stroke-width="1.5" />

                        {{-- Paths: entrance → rooms → assessment --}}
                        <path d="M68 320 L165 320 L165 194" fill="none" stroke="#0f766e" stroke-width="3" stroke-dasharray="8 4" marker-end="url(#pathArrow)" opacity="0.9" />
                        <path d="M205 194 L205 230 L355 230 L355 194" fill="none" stroke="#0f766e" stroke-width="3" stroke-dasharray="8 4" marker-end="url(#pathArrow)" opacity="0.9" />
                        <path d="M395 194 L395 230 L545 230 L545 194" fill="none" stroke="#0f766e" stroke-width="3" stroke-dasharray="8 4" marker-end="url(#pathArrow)" opacity="0.9" />
                        <path d="M585 194 L585 230 L730 230 L730 180" fill="none" stroke="#0f766e" stroke-width="3" stroke-dasharray="8 4" marker-end="url(#pathArrow)" opacity="0.9" />

                        {{-- Yes → Consultation --}}
                        <path d="M730 180 L730 320 L770 320 L770 380" fill="none" stroke="#16a34a" stroke-width="3" stroke-dasharray="8 4" marker-end="url(#pathArrowGreen)" opacity="0.9" />
                        <text x="790" y="310" fill="#16a34a" font-size="11" font-weight="700" font-family="system-ui,sans-serif">Yes</text>

                        {{-- Consultation → Pharmacy --}}
                        <path d="M660 500 L420 500 L420 400" fill="none" stroke="#7c3aed" stroke-width="3" stroke-dasharray="8 4" marker-end="url(#pathArrowViolet)" opacity="0.9" />
                        <text x="530" y="488" fill="#7c3aed" font-size="10" font-weight="700" font-family="system-ui,sans-serif">Medicine referred</text>

                        {{-- Pharmacy → Exit --}}
                        <path d="M280 500 L160 500 L160 420" fill="none" stroke="#475569" stroke-width="3" stroke-dasharray="8 4" marker-end="url(#pathArrowSlate)" opacity="0.9" />

                        {{-- No → Pharmacy --}}
                        <path d="M730 180 L730 280 L420 280 L420 396" fill="none" stroke="#ea580c" stroke-width="2.5" stroke-dasharray="4 4" opacity="0.75" marker-end="url(#pathArrowOrange)" />
                        <text x="560" y="272" fill="#c2410c" font-size="10" font-weight="700" font-family="system-ui,sans-serif">No → Pharmacy</text>

                        {{-- No → Exit --}}
                        <path d="M700 180 L700 350 L160 350 L160 416" fill="none" stroke="#94a3b8" stroke-width="2" stroke-dasharray="3 5" opacity="0.7" marker-end="url(#pathArrowSlate)" />
                        <text x="400" y="342" fill="#64748b" font-size="10" font-weight="600" font-family="system-ui,sans-serif">No → Exit</text>

                        {{-- No medicine → Exit --}}
                        <path d="M660 560 L160 560 L160 560" fill="none" stroke="#64748b" stroke-width="2" stroke-dasharray="3 5" opacity="0.55" />
                        <text x="400" y="550" fill="#64748b" font-size="10" font-family="system-ui,sans-serif">No medicine → Exit</text>
                    </svg>
                </div>

                <div class="px-4 sm:px-6 py-4 border-t border-slate-100 bg-slate-50 space-y-3">
                    <div class="flex flex-wrap gap-4 text-xs text-slate-600">
                        <span class="inline-flex items-center gap-1.5"><span class="inline-block w-5 border-t-2 border-dashed border-teal-700"></span> Primary path</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-green-100 border border-green-600"></span> Yes → Consultation</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-orange-100 border border-dashed border-orange-500"></span> Skip consult → Pharmacy</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-slate-100 border border-dashed border-slate-400"></span> Direct exit</span>
                    </div>
                    <ol class="grid sm:grid-cols-2 gap-x-6 gap-y-1 text-xs text-slate-600 list-decimal list-inside">
                        <li>Register at Registration</li>
                        <li>Vitals (Part 1 &amp; 2)</li>
                        <li>Blood Glucose</li>
                        <li>Encoder toggles consultation (or pharmacy / exit)</li>
                        <li>Consultant queues, consults, sets disposition</li>
                        <li>Pharmacy if medicine was referred</li>
                        <li>Patient exits</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
