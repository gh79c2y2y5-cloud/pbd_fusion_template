# R360 CORE — canonical math and function registry (recovered v0.1)
Status: RECOVERED DEFINITIONS; NOT A VERIFIED EXECUTABLE IMPLEMENTATION.
Sources: PBD_Source_of_Truth_Protocol_v1.txt; EQUATION_REGISTER.md; PBD_R360_Evidence_Addendum_Breadcrumbs_3D_Flight_2026-09-26.docx; PBD_R360_Character_Personalization_Case_Study.docx; R360_Stage8_Evidence_Complete_2026-09-30.pdf.
This is the single starting point for R360 math retrieval. Do not replace source evidence; retain provenance and private/public classification.

## Function roles
Barry = fixed defined reference center.
Norm = PRE / NORM / POST comparison, low/high normalization, preserve units and original reference.
Missi (also called Missy) = flip and reverse specialist: inverse path, swap source/sink, reverse transformations, detect hidden assumptions, signed residual verification.
Sequence: PRE-NORM -> Norm -> Missi -> Norm return check -> compare with PRE-NORM.
IMPORTANT: Function roles are documented, but executable function bodies have not been located or verified.

## Core equation registry (recovered)
R360-001 signed residual: R = observed - target (sign convention requires approval).
R360-002 exact/working reconstruction: x_exact = x_working + R_x where R_x = exact - working. Do not confuse with R360-001.
R360-003 additive compatible residuals: R_total = sum(R_i) (same units/frame).
R360-004 inverse: T^-1(T(S)) = S, only for invertible T.
R360-005 state return: S0 -> S1 -> ... -> Sn -> S0; not necessarily physical return.
R360-006 midpoint: C = (a+b)/2.
R360-007 scale: R_n = X*2^n; inverse /2^n.
R360-008 signed closure: C_A = input - reconstructed.
R360-009 normalized closure: C = 1 - abs(R)/abs(S), S != 0; a score, not a probability.
R360-010 sqrt drill: sqrt(137)^2 = 137.
R360-011 area-volume: L=sqrt(137); A=L^2; V=L^3.
R360-012 root/cube: B=137^(2/3); sqrt(B^3)=137.
R360-013 exact full-turn reciprocal: 360*(1/360)=1.
R360-014 barycenter: r_CM = sum(m_i*r_i)/sum(m_i), sum(m_i)!=0.
R360-015 orientation transforms: X_U = T_(U<-E) ... T_(D<-...) X; matrices/frames not yet verified.
R360-016 state transition: Delta E_state > epsilon_state AND new stable basin; threshold undefined.
R360-017 record: exact expression, numeric value, signed residual, UID/ParentUID, uncertainty, provenance.

## Core state record
StateUID, ParentUID, frame, source, units, PRE, NORM, POST, operation, inverse, signed residual, uncertainty, precision, lineage, version, classification.
Maintain 55 decimal places when requested; return-to-1 is a TEST CONDITION, not a guaranteed outcome. Record the preceding value if reached. Keep private breadcrumb paths out of public comparison.

## Validation
Separate mathematical/coordinate closure from physical conservation, singularity, or performance claims. No proof of Navier-Stokes or other Millennium problems is asserted.
Open tasks: audit ALL site products, R&D pages, archives and code; deduplicate formulas; reconcile signs; locate executable Norm and Missi; regression-test against external benchmarks.
