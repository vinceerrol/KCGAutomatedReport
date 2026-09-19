# UI & UX Standards

This workspace strictly enforces modern SaaS-grade UI/UX best practices (Linear / Notion / Stripe tier) for Vue 3 + Tailwind CSS.

## 1. Visual Hierarchy & Design Language
- **Palette**: Clean slate/neutral dark-and-light friendly tokens (`bg-slate-50`, `text-slate-900`, `border-slate-200/80`, dark: `bg-slate-950`, `text-slate-100`, `border-slate-800`).
- **Accent**: Modern professional indigo/blue (`indigo-600` primary, `indigo-500` hover, `indigo-50` subtle tint).
- **Status Colors**:
  - `SCHEDULED`: Amber / yellow (`bg-amber-50 text-amber-700 border-amber-200`)
  - `IN PROGRESS`: Blue / indigo (`bg-blue-50 text-blue-700 border-blue-200`)
  - `DONE`: Emerald / green (`bg-emerald-50 text-emerald-700 border-emerald-200`)
  - `OVERDUE`: Crimson / rose (`bg-rose-50 text-rose-700 border-rose-200 font-semibold`)
- **Typography**: Crisp sans font hierarchy (`font-sans`), distinct weights, no body text below 13px/14px.
- **Elevation**: Subtle borders (`border border-slate-200/80 dark:border-slate-800/80`) paired with soft shadow (`shadow-xs` / `shadow-sm`) rather than dated heavy dropshadows.

## 2. Mobile-First & Touch Ergonomics
- **44px Minimum Touch Target**: All clickable buttons, inputs, and list items must have at least `min-h-[44px]` or `p-2.5` to prevent mis-clicks on mobile devices.
- **Bottom Navigation / Thumb Zone**: Primary navigation on mobile should be reachable at the bottom of the screen with a prominent `+ Create Task` button.
- **Bottom Sheets for Modals on Mobile**: Slide-up sheets or drawer components on phone viewports instead of tiny centered modals with clipped overflow.

## 3. Rapid Task Creation UX (10–20 Second Target)
- **Autofocus**: First field (Task Title) immediately focused upon opening the modal.
- **Smart Team Filtering**: Selecting a team instantly narrows member options and displays live workload badges (e.g. `2 active tasks`, `1 overdue`).
- **Keyboard Shortcuts**: Support `Ctrl/Cmd + Enter` to instantly submit the task from anywhere in the form.
- **Sensible Defaults**: Default priority = Normal, default start = "Start Now", default deadline = End of day (5:00 PM).

## 4. State Completeness
- **Loading Skeletons**: Never show a blank white/black screen while fetching tasks or stats. Use pulse skeletons (`animate-pulse bg-slate-200 dark:bg-slate-800 rounded`).
- **Empty States**: Every filter or tab must have an encouraging empty state with a Lucide icon and clear action button.
- **Instant Optimistic UI**: Clicking "Mark Done" or "Start Task" immediately reflects in the UI with a subtle toast (`vue-sonner`), updating in the background without frozen loading spinners.
- **Lucide Icons**: Use consistent stroke widths (`stroke-width="1.75"`) across all icons.
